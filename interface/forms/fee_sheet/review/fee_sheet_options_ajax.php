<?php
require_once("../../../globals.php");
require_once("fee_sheet_options_queries.php");
if(!acl_check('acct', 'bill'))
{
    header("HTTP/1.0 403 Forbidden");    
    echo "Not authorized for billing";   
    return false;
}

$dbi = NewADOConnection("mysqli"); 
$dbi->PConnect($host, $login, $pass, $dbase);
$fso=load_fee_sheet_options($dbi);
$retval=array();
$retval['fee_sheet_options']=$fso;
echo json_encode($retval);
?>
