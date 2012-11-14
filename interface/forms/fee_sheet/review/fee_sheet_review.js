/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var review_ajax=webroot+"/interface/forms/fee_sheet/review/fee_sheet_ajax.php";
var fee_sheet_new=webroot+"/interface/forms/fee_sheet/new.php";
var ajax_fee_sheet_options=webroot+"/interface/forms/fee_sheet/review/fee_sheet_options_ajax.php";
var fs_choose_justify=webroot+"/interface/forms/fee_sheet/review/fee_sheet_choose_justify.js";
var display_table_selector="table[cellspacing='5']";
var justify_ajax=webroot+"/interface/forms/fee_sheet/review/fee_sheet_justify.php";
function diagnosis(desc,code,code_type)
{
    this.description=desc;
    this.code=code;
    this.code_type=code_type;
    this.key=function(){return this.code_type+"|"+this.code;}
    return this;
}

function procedure(desc,code,code_type,modifiers,units,fee,justify)
{
    this.description=desc;
    this.code=code;
    this.code_type=code_type;
    this.modifiers=modifiers;
    this.units=units;
    this.fee=fee;
    this.justify=justify;
    return this;
}

function save_fee_form(callback)
{
    top.restoreSession();
    var form_data=$("form").serialize()+"&bn_save='Save'";
    $.post(fee_sheet_new,form_data,callback);
}
function ajax_refresh(data)
{
        var fee_sheet_info=$(data);
        var table=fee_sheet_info.find(display_table_selector);
        $(display_table_selector).replaceWith(table);  
        
        // need refresh the diagnosis list
        var diag_regex=new RegExp("diags.push(.*);\n","g");
        var diags_matches=data.match(diag_regex);
        if(diags_matches!=null)
            {
                diags=new Array(); // clear the existing diags array
                for(var i=0;i<diags_matches.length;i++)
                    {
                        eval(diags_matches[i]);
                    }                
            }
        var justifications=$("select[onchange='setJustify(this)']");
        justifications.change();
        setup_justify_events($(display_table_selector));    
}
function refresh_codes()
{
    top.restoreSession();
    $.post(fee_sheet_new,{},function(data){
        ajax_refresh(data);
    });
}

function add_codes(diag_list,proc_list)
{
    top.restoreSession();
    $.post(review_ajax,{
        pid: pid,
        encounter: enc,
        task: 'add_diags',
        diags: JSON.stringify(diag_list),
        procs: JSON.stringify(proc_list)
    },
    function(data)
        {
            refresh_codes();
        }

    );
}

function cancel_event(event)
{
    event.preventDefault();
    review_display().hide();
}
function parse_justify(choices)
{
    var retval="";
    
    choices.each(function(idx,elem)
    {
        var choice=$(elem);
        retval+=choice.attr("code_type")+"|"+choice.attr("code")+":"
    });
    return retval;
}
function procedure_from_row(tr)
{
    var select=tr.find("select.procedure_code");
    var choice=select.find(":selected");   
    var desc=choice.attr("description");
    var code=choice.attr("code");
    var code_type=choice.attr("code_type");
    var modifiers=tr.find("td.modifiers").text();
    var units=tr.find("td.units").text();
    var fee=tr.find("td.fee").text();
    var justifyChoices=tr.find("td.justify input:checked");
    var justify=parse_justify(justifyChoices);
    return new procedure(desc,code,code_type,modifiers,units,fee,justify);
}
function add_event(event)
{
    event.preventDefault();
    review_display().hide();
    var elements=review_display().find("table.diagnosis_display input:checked");
    var diagnoses=new Array();
    elements.each(function(idx,elem)
    {
       var tr=$(elem).parents("tr");
       var diag=new diagnosis(tr.attr("description"),tr.attr("code"),tr.attr("code_type"));
       diagnoses.push(diag);
       
    });
    var procedures=new Array();
    var procedureElements=review_display().find("table.procedure_display input.procedure_selector:checked");
    procedureElements.each(function(idx,elem){
       var tr=$(elem).parents("tr.procedure");
       procedures.push(procedure_from_row(tr));
    });
    save_fee_form(function(data){add_codes(diagnoses,procedures);});
    
}

var fee_sheet_options=null;
function get_fee_sheet_options()
{
    if(fee_sheet_options==null)
    {
        var fso=$.ajax(ajax_fee_sheet_options,{type:"GET",async:false,dataType:"json"});
        fee_sheet_options=JSON.parse(fso.responseText)['fee_sheet_options'];
    }
    return fee_sheet_options;
}

function procedure_choice_event(evt)
{
    var tr=$(this).parents("tr.procedure");
    var select=$(this);
    var selected=$(this).find(":selected");
    if(selected.is(".original"))
        {
            select.addClass("original");
        }
    else
        {
            select.removeClass("original");
        }
    if(selected.is(".original_category"))
        {
            select.addClass("original_category")
        }
        else
        {
            select.removeClass("original_category");
        }
    tr.find("td.fee").text(selected.attr("price"));
}

function render_procedure_select(data,fso,parent)
{
    var select=$("<select class='procedure_code'></select>");
    var found=false;
    var selected_category;
    for(var idx=0;idx<fso.length;idx++)
        {
            var option=fso[idx];
            var opt=$("<option></option>");
            opt.text(option.code+":"+option.description);
            opt.attr("code",option.code);
            opt.attr("code_type",option.code_type);
            opt.attr("price",option.price);
            opt.attr("description",option.description);
            opt.attr("category",option.category);
            if((option.code==data.code)&&(option.code_type==data.code_type))
                {
                    found=true;
                    opt.attr("selected","selected");
                    opt.addClass("original");
                    selected_category=option.category;
                }
                else
                {
                    opt.addClass("choice");
                }
            select.append(opt);
        }
        if(!found)
            {
                
            }
   select.find("[category='"+selected_category+"']").addClass("original_category");
   select.on({change:procedure_choice_event});
   select.addClass("original");
   select.addClass("original_category");
   parent.append(select);
}

function create_code(code,idx)
{
    var code_data=code.split("|");
    var span=$("<span></span>")
    var cb=$("<input type='checkbox' checked='checked'/>").appendTo(span);
    var label=$("<span>"+code_data[1]+"</span>").appendTo(span);
    cb.attr("code_type",code_data[0]);
    cb.attr("code",code_data[1]);
    span.attr("order",idx);
    return span;
}
function render_justify(data,parent)
{
    var codes=data.justify.split(":")
    for(idx=0;idx<codes.length;idx++)
        {
            var cur=codes[idx];
            if(cur!="")
            {
                create_code(codes[idx]).appendTo(parent);            
            }
        }
    parent.text();
}
function render_procedure(data,fso,parent)
{
        var tr=$("<tr class='procedure'></tr>");
        tr.attr("code",data.code);
        tr.attr("code_type",data.code_type);
        tr.attr("description",data.description);
        tr.attr("justify",data.justify);
        var selected="";
        if(data.selected=='1')
            {
                selected="checked=''"
            }
        var tdcb=$("<td><input class='procedure_selector' type='checkbox' "+selected+"'/></td>")
        tr.append(tdcb);
        var td_code=$("<td></td>").appendTo(tr);
        render_procedure_select(data,fso,td_code);
        var tdFee=$("<td class='fee'>"+data.fee+"</td>").appendTo(tr);
        var tdMod=$("<td class='modifiers'></td>").appendTo(tr);
        var inputMod=$("<input class='modifiers' type='text' value='"+data.modifiers+"'/>").appendTo(tdMod);
        inputMod.attr("size",data.mod_size);
        var tdUnits=$("<td class='units'>"+data.units+"</td>").appendTo(tr);
        var tdJust=$("<td class='justify'></td>").appendTo(tr);
        render_justify(data,tdJust);
        parent.append(tr);
    
}

function render_procedures(data,parent)
{

    if(!data)
        {
            return;
        }
    var table=$("<table class='procedure_display'></table>").appendTo(parent);
    var thead=$("<thead></thead>").appendTo(table);
    var tbody=$("<tbody></tbody>").appendTo(table);
    var header=$("<tr></tr>");
    thead.append(header);
    var th=$("<th colspan='2' class='first_header'>Procedure</th>");
    header.append(th);
    var fee=$("<th>Fee</th>").appendTo(header);
    var modifier=$("<th>Modifiers</th>").appendTo(header);
    var units=$("<th>Units</th>").appendTo(header);
    var justify=$("<th>Justify</th>").appendTo(header);
    
    var fso=get_fee_sheet_options();
    for(idx=0;idx<data.length;idx++)
    {
        render_procedure(data[idx],fso,tbody);
    }

}
function render_diagnoses(data,parent)
{
    var table=$("<table class='diagnosis_display'></table>");
    var tbody=$("<tbody></tbody>");
    var header=$("<tr></tr>");
    tbody.append(header);
    var th=$("<th colspan='3' class='first_header'>Diagnosis</th>");
    header.append(th);
    table.append(tbody);
    parent.append(table);
    for(idx=0;idx<data.length;idx++)
    {
        var diag=data[idx];
        var tr=$("<tr></tr>");
        tr.attr("code",diag.code);
        tr.attr("code_type",diag.code_type);
        tr.attr("description",diag.description);
        var selected="";
        if(diag.selected=='1')
            {
                selected="checked=''"
            }
        var tdcb=$("<td><input type='checkbox' "+selected+"'/></td>")
        tr.append(tdcb);
        var td_code=$("<td>"+diag.code+"</td>");
        tr.append(td_code);
        var td=$("<td>"+diag.description+"</td>")
        tr.append(td);
        tbody.append(tr);
    }
    var add=$("<button>Add Codes</button>");
    var cancel=$("<button>Cancel</button>");
    var container=$("<div class='controls'></div>");
    container.append(add);
    container.append(cancel);
    add.on({click:add_event});
    cancel.on({click:cancel_event})
    parent.append(container);
    return table;
}
function render_encounters(encounters,parent,selected)
{
    if(!encounters)
        {
            return;
        }
    var div_encounter=$("<div id='div_review_encounters'></div>")
    var select=$("<SELECT id='old_encounters'></SELECT>");
    for(idx=0;idx<encounters.length;idx++)
        {
            var cur=encounters[idx];
            var option=$("<option value='"+cur.id+"'>"+cur.date +"</option>")
            if(cur.id==selected)
                {
                    option.attr("selected","selected");
                }
            select.append(option);
        }
        var display=review_display().find(".results");
        select.on({change:
            function()
            {
                    var encID=$(this).val();
                    top.restoreSession();
                    $.post(review_ajax,{
                        pid: pid,
                        encounter: enc,
                        mode: 'encounters',
                        prev_encounter:encID,
                        task: "retrieve"
                        },function(data){render_display(data,display);
                        },"json");
            }
        });
    div_encounter.append(select);
    parent.append(div_encounter);
}
function render_display(data,display)
{
        display.html("");
        render_encounters(data.encounters,display,data.prev_encounter);
        render_procedures(data.procedures,display);
        render_diagnoses(data.issues,display)    
        review_display().show();
}
function populate_review_display(mode,display)
{
    top.restoreSession();    
    
    $.post(review_ajax,{
        pid: pid,
        encounter: enc,
        mode: mode,
        task: "retrieve"
    },function(data){render_display(data,display);
    },"json");
}
function review_display()
{
    var display=$(".review_display");
    if(display.length==0)
    {
        display=$("<div class='review_display'></div>");
        review.after(display);
    }
    return display;
}

function change_mode()
{
    $(this).siblings("span").removeClass("selected");
    $(this).addClass("selected");
    var display=review_display().find(".results");
    populate_review_display($(this).attr("mode"),display);
}
function review_event(event)
{
    event.preventDefault();
    display=review_display();
    display.html("");
    display.append("<span mode='encounters' class='selected'>Prior Encounters</span><span mode='issues'>Issues</span><span mode='common'>Common Diagnoses</span>");
    display.children("span[mode]").on({click: change_mode});
    var results=$("<div class='results'></div>");
    display.append(results);
    populate_review_display('encounters',results);
}
function add_review_controls()
{
    var title=$("span.title");
    review=$("<button>Review</button>");
    review.on({click:review_event});
    var td=$("<td class='review_td'></td>");
    td.append(review)
    var copay=$("td > input:first").parent();
    copay.before(td);
    
    $(document).append("<script type='text/javascript' src='"+fs_choose_justify+"'></script")
}

$(document).ready(add_review_controls);