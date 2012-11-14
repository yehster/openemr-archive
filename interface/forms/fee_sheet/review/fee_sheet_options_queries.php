<?php
require_once("$srcdir/formatting.inc.php");

class fee_sheet_option
{
    function __construct($c,$ct,$desc,$price,$category)
    {
        $this->code=$c;
        $this->code_type=$ct;
        $this->description=$desc;
        $this->price=$price;
        $this->category=$category;
    }
    public $code;    
    public $code_type;
    public $description;
    public $price;
    public $fee_display;
    public $category;

}

function load_fee_sheet_options($db)
{
    $clFSO_code_type='substring_index(fso.fs_codes,"|",1)';
    $clFSO_code='replace(substring_index(fso.fs_codes,"|",-2),"|","")';
    
    $sql= "SELECT codes.code,code_types.ct_key as code_type,codes.code_text,pr_price,fso.fs_category"
        . " FROM fee_sheet_options as fso, codes, prices,code_types "
        . " WHERE codes.code=".$clFSO_code
        . " AND code_types.ct_key=".$clFSO_code_type
        . " AND codes.code_type=code_types.ct_id"
        . " AND prices.pr_id=codes.id"
        . " ORDER BY fso.fs_category,fso.fs_option";
    
    $results=$db->GetAll($sql);

    $retval=array();
    foreach($results as $res)
    {
        $fso=new fee_sheet_option($res['code'],$res['code_type'],$res['code_text'],$res['pr_price'],$res['fs_category']);
        $retval[]=$fso;
    }
    
    return $retval;
}
?>
