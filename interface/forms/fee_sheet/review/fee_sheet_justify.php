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
if(isset($_REQUEST['billing_id']))
{
    $billing_id=$_REQUEST['billing_id'];
}
if($task=='retrieve')
{
    $retval=array();
    $patient=issue_diagnoses($dbi,$req_pid,$req_encounter);      
    $common=common_diagnoses($dbi);
    $retval['patient']=$patient;
    $retval['common']=$common;
    $fee_sheet_diags=array();
    $fee_sheet_procs=array();
    fee_sheet_items($dbi,$req_pid,$req_encounter,$fee_sheet_diags,$fee_sheet_procs);
    $retval['current']=$fee_sheet_diags;
    echo json_encode($retval);
    return;
}
if($task=='update')
{
    if(isset($_REQUEST['diags']))
    {
        $json_diags=json_decode($_REQUEST['diags']);
    }    
    foreach($json_diags as $diag)
    {
        $diags[]=new code_info($diag->{'code'},$diag->{'code_type'},$diag->{'description'});
    }
    $dbi->StartTrans();
    create_diags($dbi,$req_pid,$req_encounter,$diags);
    update_justify($dbi,$req_pid,$req_encounter,$diags,$billing_id);
    $dbi->CompleteTrans();
}

?>
