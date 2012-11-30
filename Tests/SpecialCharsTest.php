<?php
include_once("../library/htmlspecialchars.inc.php");
$test_string="Single Quote(\')  Double Quote(\")  Less Than (<) Greater Than (>) Apersand (&)";
function generate_onclick_anchor($id,$message,$anchor_text)
{

    echo "<A HREF=\"#\" val=".$id."\" onclick=\"window.alert(\"".$message."\")\">".$anchor_text."</a>-literal double quotes<br>\n";
    echo "<A HREF=\"#\" val=".$id."\" onclick=\"window.alert(&quot;".$message."&quot;)\">".$anchor_text."</a>-entity double quotes<br>\n";
    echo "<A HREF=\"#\" val=".$id."\" onclick=\"window.alert('".$message."')\">".$anchor_text."</a>-literal single quotes<br>\n";
    echo "<A HREF=\"#\" val=".$id."\" onclick=\"window.alert(&apos;".$message."&apos;)\">".$anchor_text."</a>-entity single quotes<br>\n";   
    echo "<br>";
    
}

function generate_data_anchor($id,$message,$anchor_text)
{

    echo "<A HREF=\"#\" val=".$id."\" data=\"".$message."\"\">".$anchor_text."</a>-literal double quotes<br>\n";
    echo "<A HREF=\"#\" val=".$id."\" data=&quot;".$message."&quot;\">".$anchor_text."</a>-entity double quotes<br>\n";
    echo "<A HREF=\"#\" val=".$id."\" data='".$message."'\">".$anchor_text."</a>-literal single quotes<br>\n";
    echo "<A HREF=\"#\" val=".$id."\" data=&apos;".$message."&apos;\">".$anchor_text."</a>-entity single quotes<br>\n";   
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
On Click as Attribute<br>
<?php 
    generate_onclick_anchor("no escapes",$test_string,"no_escapes"); 
    generate_onclick_anchor("addslashes",addslashes($test_string),"addslashes"); 
    generate_onclick_anchor("attr",attr($test_string),"attr"); 
    generate_onclick_anchor("addslashes-attr",addslashes(attr($test_string)),"addslashes(attr)"); 
    generate_onclick_anchor("attr-addslashes",attr(addslashes($test_string)),"attr(addslashes)"); 
?>
Mouse Overs<br>
<div id="mouse_overs">
<?php
    generate_data_anchor("no escapes",$test_string,"no_escapes"); 
    generate_data_anchor("addslashes",addslashes($test_string),"addslashes"); 
    generate_data_anchor("attr",attr($test_string),"attr"); 
    generate_data_anchor("addslashes-attr",addslashes(attr($test_string)),"addslashes(attr)"); 
    generate_data_anchor("attr-addslashes",attr(addslashes($test_string)),"attr(addslashes)"); 
    
    
    
?>
    <script>
    function case_1()
    {
        var msg="<?php echo $test_string?>";
        $("#info").html(msg);
        window.alert(msg);
        return false;
    }
    </script>
    <script>
    function case_2()
    {
        var msg="<?php echo addslashes($test_string)?>";
        $("#js_info").html(msg);
        window.alert(msg);
        return false;
    }
    </script>
    <script>
    function case_3()
    {
        var msg="<?php echo attr($test_string)?>";
        $("#js_info").html(msg);
        window.alert(msg);
        return false;
    }
    </script>
    <script>
    function case_4()
    {
        var msg="<?php echo attr(addslashes($test_string));?>";
        $("#js_info").html(msg);
        window.alert(msg);
        return false;
    }
    </script>
    <script>
    function case_5()
    {
        var msg="<?php echo addslashes(attr($test_string));?>";
        $("#js_info").html(msg);
        window.alert(msg);
        return false;
    }
    </script>

    
</div>
Javascript<br>
<a href="#" onclick="case_1()">No Escape</a><br>
<a href="#" onclick="case_2()">Add Slashes</a><br>
<a href="#" onclick="case_3()">Attr</a><br>
<a href="#" onclick="case_4()">attr(addslashes)</a><br>
<a href="#" onclick="case_5()">addslashes(attr)</a><br>

<div id="info" style="position:fixed; top:0px;left:0px;opacity:1;background:yellow;"></div>
<div id="js_info" style="position:fixed; top:0px;left:0px;opacity:1;background:lightblue;"></div>

<script>
    $("#bind_click a").add("#mouse_overs a").on({click: function(){window.alert($(this).attr("data"));},
               mouseenter: function(){$("#info").html($(this).attr("data"));},
               mouseleave: function(){$("#info").html("");}
                });
</script>