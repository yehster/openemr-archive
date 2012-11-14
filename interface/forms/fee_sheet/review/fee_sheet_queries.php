<?php
require_once("$srcdir/formatting.inc.php");
class code_info
{
    function __construct($c,$ct,$desc,$selected=true)
    {
        $this->code=$c;
        $this->code_type=$ct;
        $this->description=$desc;
        $this->selected=$selected;
    }
    public $code;    
    public $code_type;
    public $description;
    public $selected;

    public function getKey()
    {
        return $this->code_type."|".$this->code;
    }

    public function test()
    {
        error_log($this->code.":".$this->code_type.":".$this->description);
    }
    public function addArrayParams(&$arr)
    {
        array_push($arr,$this->code_type,$this->code,$this->description);
    }
}

class procedure extends code_info
{
    function __construct($c,$ct,$desc,$fee,$justify,$modifiers,$units,$mod_size,$selected=true)
    {
        parent::__construct($c,$ct,$desc,$selected);
        $this->fee=$fee;
        $this->justify=$justify;
        $this->modifiers=$modifiers;
        $this->units=$units;
        $this->mod_size=$mod_size;
    }
    public $fee;
    public $justify;
    public $modifiers;
    public $units;

    //modifier, units, fee, justify
    
    public function addProcParameters(&$params)
    {
        array_push($params,$this->modifiers,$this->units,$this->fee,$this->justify);
    }
    
}

class encounter_info
{
    function __construct($id,$date)
    {
        $this->id=$id;
        $this->date=$date;
    }
    
    public $id;
    public $date;
    
    function getID()
    {
        return $this->id;
    }
}
function create_diags($dbi,$req_pid,$req_encounter,$diags)
{
    $authorized=1;// Need to fix this. hard coded for now
    $provid="";
    $rowParams="(NOW(), ?, ?, ?, ?,". // date, encounter, code_type,code, code_text
            " ?, ?, ?, ?,". // pid, authorized, user, groupname
            "1, 0, ?," .    // activity, billed, provider_id
            " '', '', '0.00', '', '', '')"; // modifier, units,fee,ndc_info,justify,notecodes
    $sql = "insert into billing (date, encounter, code_type, code, code_text, " .
    "pid, authorized, user, groupname, activity, billed, provider_id, " .
    "modifier, units, fee, ndc_info, justify, notecodes) values ";
    $sql.=$rowParams;
    $findRow= " SELECT count(*) as num FROM BILLING where activity=1 AND encounter=? AND pid=? and code_type=? and code=? and code_text=?";
    $not_first=false;
    foreach($diags as $diag)
    {
        $find_params=array($req_encounter,$req_pid);
        $diag->addArrayParams($find_params);
        $search=$dbi->GetOne($findRow,$find_params);
        if($search===false)
        {
            error_log($findRow);
            error_log($dbi->ErrorMsg());
            return;
        }
        if($search==0)
        {
            $bound_params=array();
            array_push($bound_params,$req_encounter);
            $diag->addArrayParams($bound_params);
            array_push($bound_params,$req_pid,$authorized,$_SESSION['authId'],$_SESSION['authProvider'],$provid);       
            $not_first=true;
            print_r($bound_params);
            error_log($sql);
            $res=$dbi->Execute($sql,$bound_params);
            if($res===false)
            {
                error_log($dbi->ErrorMsg());
            }
        }
        // TO DO: Synch with encounter issues
    }
}

function create_procs($dbi,$req_pid,$req_encounter,$procs)
{
    $authorized=1;// Need to fix this. hard coded for now
    $provid="";
    $sql = "insert into billing (".
            "date,      encounter,  code_type,  code,".
            "code_text, pid,        authorized, user,".
            "groupname, activity,   billed,     provider_id, " .
            "modifier,  units,      fee,        ndc_info, ".
            "justify,   notecodes".
            ") values ";    
    $param="(NOW(),?,?,?,". // date, encounter, code_type, code
            "?,?,?,?,".     // code_text,pid,authorized,user
            "?,1,0,?,".     // groupname,activity,billed,provider_id
            "?,?,?,'',".     // modifier, units, fee, ndc_info
            "?,'')";        // justify, notecodes
    foreach($procs as $proc)
    {
        $insert_params=array();
        array_push($insert_params,$req_encounter);
        $proc->addArrayParams($insert_params);
        array_push($insert_params,$req_pid,$authorized,$_SESSION['authId'],$_SESSION['authProvider'],$provid);
        $proc->addProcParameters($insert_params);
        error_log($sql.$param);
        $res=$dbi->Execute($sql.$param,$insert_params);
        if($res===false)
        {
            error_log($dbi->ErrorMsg());
        }
        
    }
}

function issue_diagnoses($dbi,$pid,$encounter)
{
    $retval=array();
    $parameters=array($encounter,$pid);
    $sql= "SELECT l.diagnosis as diagnosis,l.title as title, NOT ISNULL(ie.encounter) as selected ".
          " FROM lists as l" .
          " LEFT JOIN issue_encounter as ie ON ie.list_id=l.id AND ie.encounter=?".
          " WHERE l.type='medical_problem'".
          " AND l.pid=?" . // 1st parameter pid
          " ORDER BY ie.encounter DESC,l.id";
    error_log($sql);
    $results=$dbi->GetAll($sql,$parameters);
    foreach($results as $res)
    {
        $title=$res['title'];
        $diagnosis=explode(":",$res['diagnosis']);
        $code=$diagnosis[1];
        $code_type=$diagnosis[0];
        $retval[]=new code_info($code,$code_type,$title,$res['selected']);
    }
    error_log(count($results));
    error_log($dbi->ErrorMsg());
    auditSQLEvent($sql,($results===false) ? false : true,$parameters);
    return $retval;
}

function common_diagnoses($dbi,$limit=10)
{
    $retval=array();
    $parameters=array($limit);
    $sql="SELECT code_type, code, code_text,count(code) as num " .
         " FROM BILLING WHERE code_type in ('ICD9')" .
         " GROUP BY code_type,code,code_text ORDER BY num desc LIMIT ?";
    $results=$dbi->GetAll($sql,$parameters);
    foreach($results as $res)
    {
        $title=$res['code_text'];
        $code=$res['code'];
        $code_type=$res['code_type'];
        $retval[]=new code_info($code,$code_type,$title,0);
    }
    return $retval;
}

function fee_sheet_items($dbi,$pid,$encounter, &$diagnoses,&$procedures)
{
    //$issues=issue_diagnoses($dbi,$req_pid,$req_encounter);
    $param=array($encounter);
    $sql="SELECT code,code_type,code_text,fee,modifier,justify,units,ct_diag,ct_fee,ct_mod " 
          ." FROM billing, code_types as ct " 
          ." WHERE encounter=? AND billing.activity>0 AND ct.ct_key=billing.code_type AND ct_active=1"
          ." ORDER BY id";
    $results=$dbi->GetAll($sql,$param);
    foreach($results as $res)
    {
        $code=$res['code'];
        $code_type=$res['code_type'];
        $code_text=$res['code_text'];
        if($res['ct_diag']=='1')
        {
            $diagnoses[]=new code_info($code,$code_type,$code_text);
        }
        else if($res['ct_fee']==1)
        {
            $fee=$res['fee'];
            $justify=$res['justify'];
            $modifiers=$res['modifier'];
            $units=$res['units'];
            $selected=true;
            $mod_size=$res['ct_mod'];
            $procedures[]=new procedure($code,$code_type,$code_text,$fee,$justify,$modifiers,$units,$mod_size,$selected);
        }
    }
}


function select_encounters($dbi,$pid,$encounter)
{
    $retval=array();
    $parameters=array($pid,$encounter);
    $sql="SELECT DATE(date) as date,encounter " .
         " FROM form_encounter " .
         " WHERE pid=? and encounter!=? " .
         " ORDER BY date DESC";
    $results=$dbi->GetAll($sql,$parameters);
    foreach($results as $res)
    {
        $retval[]=new encounter_info($res['encounter'],$res['date']);
    }
    return $retval;
}

function update_justify($dbi,$pid,$enc,$diags,$billing_id)
{
    $justify="";
    foreach($diags as $diag)
    {
        $justify.=$diag->getKey().":";
    }
    $sqlUpdate=" UPDATE BILLING SET justify=? "
              ." WHERE id=?";
    $params=array($justify,$billing_id);
    $dbi->Execute($sqlUpdate,$params);
}
?>
