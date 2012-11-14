<?php
$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../../../globals.php");
require_once("fee_sheet_queries.php");
if(!acl_check('acct', 'bill'))
{
    header("HTTP/1.0 403 Forbidden");    
    echo "Not authorized for billing";   
    return false;
}

$dbi = NewADOConnection("mysqli"); 
$dbi->PConnect($host, $login, $pass, $dbase);

if(isset($_REQUEST['pid']))
{
    $req_pid=$_REQUEST['pid'];
}

if(isset($_REQUEST['encounter']))
{
    $req_encounter=$_REQUEST['encounter'];
}
if(isset($_REQUEST['task']))
{
    $task=$_REQUEST['task'];
}
if($task=='retrieve')
{
   $retval=array();
   if($_REQUEST['mode']=='encounters')
    {
        $encounters=select_encounters($dbi,$req_pid,$req_encounter);
        if(isset($_REQUEST['prev_encounter']))
        {
            $prev_enc=$_REQUEST['prev_encounter'];
        }
        else 
        {
            if(count($encounters)>0)
            {
                $prev_enc=$encounters[0]->getID();
            }
        }
        $issues=array();
        $procedures=array();
        fee_sheet_items($dbi,$req_pid,$prev_enc,$issues,$procedures);
        $retval['prev_encounter']=$prev_enc;
        $retval['encounters']=$encounters;
        $retval['procedures']=$procedures;
    }
    if($_REQUEST['mode']=='issues')
    {
        $issues=issue_diagnoses($dbi,$req_pid,$req_encounter);      
    }
    if($_REQUEST['mode']=='common')
    {
            $issues=common_diagnoses($dbi);
    }
    $retval['issues']=$issues;
    echo json_encode($retval);
    return;
}
if($task=='add_diags')
{
    if(isset($_REQUEST['diags']))
    {
        $json_diags=json_decode($_REQUEST['diags']);
    }
    $diags=array();
    foreach($json_diags as $diag)
    {
        $diags[]=new code_info($diag->{'code'},$diag->{'code_type'},$diag->{'description'});
    }
    $procs=array();
    if(isset($_REQUEST['procs']))
    {
        $json_procs=json_decode($_REQUEST['procs']);
    }
    foreach($json_procs as $proc)
    {
        $procs[]=new procedure($proc->{'code'},$proc->{'code_type'},$proc->{'description'},$proc->{'fee'},$proc->{'justify'},$proc->{'modifiers'},$proc->{'units'},0);
    }
    $dbi->StartTrans();
    create_diags($dbi,$req_pid,$req_encounter,$diags);
    create_procs($dbi,$req_pid,$req_encounter,$procs);
    $dbi->CompleteTrans();
    return;
}
?>
