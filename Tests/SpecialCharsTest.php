<?php
include_once("../library/htmlspecialchars.inc.php");
$test_string="Single Quote(\')  Double Quote(\")  Less Than (<) Greater Than (>) Apersand (&)";
function generate_onclick_anchor($id,$message,$anchor_text)
{

    echo "<A HREF=\"#\" onclick=\"window.alert(\"".$message."\")\">".$anchor_text."</a>-literal double quotes<br>\n";
    echo "<A HREF=\"#\" onclick=\"window.alert(&quot;".$message."&quot;)\">".$anchor_text."</a>-entity double quotes<br>\n";
    echo "<A HREF=\"#\" onclick=\"window.alert('".$message."')\">".$anchor_text."</a>-literal single quotes<br>\n";
    echo "<A HREF=\"#\" onclick=\"window.alert(&apos;".$message."&apos;)\">".$anchor_text."</a>-entity single quotes<br>\n";
    echo "<br>";
    
}
?>
<script src="../library/js/jquery-1.7.2.min.js"></script>
<span id="bind_click">
<B>Test String</B><br>
<?php echo $test_string?>
<br>
<br>
<A id="a" href="#" data="<?php echo $test_string?>"><?php echo $test_string?></A>No Escapes<BR>
<A id="b" href="#" data="<?php echo addslashes($test_string)?>"><?php echo $test_string?></A>Add slashes<BR>
<A id="c" href="#" data="<?php echo htmlspecialchars($test_string,ENT_QUOTES)?>"><?php echo $test_string?></A>htmlspecialchars<BR>
<A id="d" href="#" data="<?php echo htmlspecialchars($test_string,ENT_QUOTES)?>"><?php echo addslashes($test_string)?></A>addslashes<BR>
<A id="g" href="#" data="<?php echo htmlspecialchars($test_string,ENT_QUOTES)?>"><?php echo htmlspecialchars($test_string,ENT_QUOTES)?></A>ENT_QUOTES<BR>
<A id="h" href="#" data="<?php echo htmlspecialchars($test_string,ENT_NOQUOTES)?>"><?php echo htmlspecialchars($test_string,ENT_NOQUOTES)?></A>ENT_NOQUOTES<BR>
<A id="i" href="#" data="<?php echo attr(attr($test_string))?>"><?php echo attr(attr($test_string))?></A>attr(attr)<BR>
<A id="j" href="#" data="<?php echo attr(addslashes($test_string))?>"><?php echo attr(addslashes($test_string))?></A>attr(addslashes)<BR>   
</span>

<br>
<?php 
    generate_onclick_anchor("no escapes",$test_string,"no_escapes"); 
    generate_onclick_anchor("no escapes",addslashes($test_string),"addslashes"); 
    generate_onclick_anchor("no escapes",attr($test_string),"attr"); 
    generate_onclick_anchor("no escapes",attr(addslashes($test_string)),"attr(addslashes)"); 

?>
<div>INFO DIV</DIV>
<div id="info"></div>
<script>
    $("#bind_click a").on({click: function(){window.alert($(this).attr("data"));},
               mouseenter: function(){$("#info").html($(this).attr("data"));}
                });
</script>