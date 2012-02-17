<script>
// +-----------------------------------------------------------------------------+
// Copyright (C) 2012 IntegralEMR LLC <kevin.y@integralemr.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Kevin Yeh <kevin.y@integralemr.com>
//
// +------------------------------------------------------------------------------+

var lblTop="<?php echo xl('Top');?>";
var lblBot="<?php echo xl('Bot');?>";
var lblLeft="<?php echo xl('Left');?>";
var lblRight="<?php echo xl('Right');?>";
function setControlNames(name1,name2)
{
    $("input:checkbox[name='cb_top']").siblings("b").replaceWith("<b>"+name1+"</b>");
    $("input:checkbox[name='cb_bot']").siblings("b").replaceWith("<b>"+name2+"</b>");
    select = $("select[name='sel_frame']");
    select.find("option[value='1']").text(name1);
    select.find("option[value='2']").text(name2);
    
}
function toggleFrameOrientation()
{
    fsr=$("#fsright",top.document);
    document.forms[0].cb_top.checked=true;
    document.forms[0].cb_bot.checked=true;
    if(fsr.attr("rows"))
        {
            fsr.attr("cols","60%,*");
            fsr.removeAttr("rows");         
            toggleFrame= function(fnum) {
                var f = document.forms[0];
                var fset = top.document.getElementById('fsright');
                if (!f.cb_top.checked && !f.cb_bot.checked) {
                if (fnum == 1) f.cb_bot.checked = true;
                else f.cb_top.checked = true;
                }
                var cols = f.cb_top.checked ? '*' :  '0';
                cols += f.cb_bot.checked ? ',*' : ',0';
                fset.cols = cols;
                fset.cols = cols;
            }
            setControlNames(lblLeft,lblRight);
        }
        else
        {
            fsr.attr("rows","60%,*");
            fsr.removeAttr("cols");
            toggleFrame= function(fnum) {
                var f = document.forms[0];
                var fset = top.document.getElementById('fsright');
                if (!f.cb_top.checked && !f.cb_bot.checked) {
                if (fnum == 1) f.cb_bot.checked = true;
                else f.cb_top.checked = true;
                }
                var rows = f.cb_top.checked ? '*' :  '0';
                rows += f.cb_bot.checked ? ',*' : ',0';
                fset.rows = rows;
                fset.rows = rows;
            }
            setControlNames(lblTop,lblBot);
        }
}
 
$("body").append("<div><div style='padding-top:8px;'>&nbsp;</div><a class='css_button' id='frameOrientationToggle' href='#'><span><?php echo xl("Frames Orientation"); ?></a></span></div>");
$("#frameOrientationToggle").click(toggleFrameOrientation);
</script>
