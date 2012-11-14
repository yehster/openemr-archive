var justify_title='Select one or more diagnosis codes to justify the service';
function cancel_justify(evt)
{
    evt.preventDefault();
    var jqElem=$(this);
    jqElem.parents(".justify_display").hide()
}
function handle_update(info,id)
{
    var rows=info.find(":checked").parent().parent();
    var diags=new Array();
    rows.each(function(idx,elem)
    {
        var jqElem=$(elem);
        diags.push(new diagnosis(jqElem.attr("description"),jqElem.attr("code"),jqElem.attr("code_type")));
    });
    top.restoreSession();
    $.post(justify_ajax,{
        pid: pid,
        encounter: enc,
        task: 'update',
        billing_id: id,
        diags: JSON.stringify(diags)
    },
    function(data)
        {
            refresh_codes();
        }

    );    
}
function update_justify(evt)
{
    evt.preventDefault();
    var justify_display=$(this).parents(".justify_display");
    handle_update(justify_display,justify_display.parent().attr("billing_id"));
    justify_display.hide();
}
function render_choices(data,parent,label,rendered,included)
{
    if(data.length==0){return;}
    var table=$("<table class='diagnosis_display'></table>");
    var tbody=$("<tbody></tbody>");
    if(label.length>0)
        {
            var header=$("<tr></tr>");
            tbody.append(header);
            var th=$("<th colspan='3' class='first_header'>"+label+"</th>");
            header.append(th);           
        }
    table.append(tbody);
    parent.append(table);
    for(idx=0;idx<data.length;idx++)
    {
        var diag=data[idx];
        var key=diag.code_type+"|"+diag.code;
        if(typeof rendered[key]=="undefined")
        {
            rendered[key]=true;
            var tr=$("<tr></tr>");
            tr.attr("code_key",key);
            tr.attr("code",diag.code);
            tr.attr("code_type",diag.code_type);
            tr.attr("description",diag.description);
            var selected="";
            if(diag.selected=='1')
                {
                    //selected="checked=''"
                }
            var tdcb=$("<td><input type='checkbox'/></td>")
            tr.append(tdcb);
            var td_code=$("<td>"+diag.code+"</td>");
            tr.append(td_code);
            var td=$("<td>"+diag.description+"</td>")
            tr.append(td);
            tbody.append(tr);

        }
    }
    return table;
}


function render_justify_choices(display,billing_id,current_justify)
{
    var mode='common';
    $.post(justify_ajax,{
    pid: pid,
    encounter: enc,
    mode: mode,
        task: "retrieve"
    },function(data){
        var rendered=new Object;
        var included=new Object;
        var current_issues=display.find(".justify_current").html("");
        render_choices(data.current,current_issues,"",rendered,included);

        var patient_issues=display.find(".patient_issues").html("");
        render_choices(data.patient,patient_issues,"Patient Issues",rendered,included);
        var common=display.find(".common").html("");
        render_choices(data.common,common,"Common",rendered);
        for(var idx=0;idx<current_justify.length;idx++)
            {
                var cur=current_justify[idx];
                var justified=display.find("tr[code_key='"+cur.key()+"']");
                justified.find("input:checkbox").attr("checked","");
            }
    },"json");

}

function parse_row_justify(row)
{
    var justify_td=row.find("td[title='"+justify_title+"']");
    var codes=justify_td.find("select").val().split(",");
    var retval=new Array();
    for(idx=0;idx<codes.length;idx++)
        {
            var cur_code_string=codes[idx];
            if(cur_code_string.length>0)
                {
                    var code_parts=cur_code_string.split("|");
                    retval.push(new diagnosis("",code_parts[1],code_parts[0]));
                }
        }
    return retval;
}
function display_justify(td,billing_id)
{
    $(".justify_display").hide();
    var jqElem=$(td);
    var display=jqElem.find(".justify_display");
    if(display.length==0)
    {
        display=$("<div class='justify_display'><div class='justify_choices'><div class='justify_search'><div class='justify_current'></div></div><div class='patient_issues'></div><div class='common'></div></div></div>").appendTo(jqElem);
        var controls=$("<div class='justify_controls'></div>").appendTo(display);
        var update=$("<button>Update</button>").appendTo(controls).on({click:update_justify});
        var cancel=$("<button>Cancel</button>").appendTo(controls).on({click:cancel_justify});
    }
    else
    {
        display.show()
    }
    var choices=display.find(".justify_choices");
    var current_justify=parse_row_justify(td.parent());
    render_justify_choices(choices,billing_id,current_justify);
}
function justify_start(evt)
{
    var td=$(this).parent();
    var billing_id=td.attr("billing_id");
    if(typeof billing_id=="undefined")
    {
        window.alert("No ID defined on row!");
        return;
    }
    display_justify(td,billing_id);
    
}
function tag_justify_rows(display)
{
    var justify_selectors=display.find("td[title='"+justify_title+"']");
    var justify_rows=justify_selectors.parent("tr")
    var justify_td=justify_rows.children("td:first-child").addClass("has_justify").attr("title","Click to choose diagnoses to justify.");
    justify_td.each(function(idx,elem){
        // This code takes the label text and "wraps it around a span for e"
        var jqElem=$(elem);
        var label=jqElem.text();
        var html=jqElem.html().substr(label.length);
        jqElem.html(html);
        $("<a class='justify_label'>"+label+"</a>").appendTo(jqElem).on({click:justify_start});
    });
    var id_fields=justify_rows.find("input[type='hidden'][name$='[id]']");
    id_fields.each(function(idx,elem){
        var jqElem=$(elem);
        var td=jqElem.parent();
        td.addClass("has_id");
        td.attr("billing_id",jqElem.attr("value"));
    });
    
}

function codeselect_and_save(selobj)
{
 var i = selobj.selectedIndex;
 if (i > 0) {
    top.restoreSession();
    var f = document.forms[0];
    f.newcodes.value = selobj.options[i].value;
    top.restoreSession();
    var form_data=$("form").serialize();
    $.post(fee_sheet_new,form_data,
        function(data)
        {
            f.newcodes.value="";
            $(selobj).find("option:selected").prop("selected",false);
            ajax_refresh(data);
            save_fee_form(refresh_codes);
        }
    ); 
 }
}
function setup_justify_events(display_table)
{
    tag_justify_rows(display_table);
    codeselect=codeselect_and_save;
    
}

setup_justify_events($(display_table_selector));