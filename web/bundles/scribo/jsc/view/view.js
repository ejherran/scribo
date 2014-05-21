// Funsion de inicio maestro 
window.onload=$init;

function $init()
{
    gId("flash-zone").onclick = hideFlash;
    gId("qck-chpass").onclick = switchChpass;
    gId("wgCambiar").onclick = saveChpass;
    gId("wgCancelar").onclick = cancelChpass;
    
    if( typeof $_init != "undefined" )
        $_init();
}

function showFlash(msg)
{
    gId("flash-zone").innerHTML += "<li>"+msg+"</li>";
    idHideTime = setTimeout(function(){ gId("flash-zone").innerHTML = ''; clearTimeout(idHideTime); }, 5000);
}

function hideFlash()
{
    this.innerHTML = '';
}

function switchChpass()
{
    if(gId("chpass").style.display != "block")
        gId("chpass").style.display = "block";
    else
        gId("chpass").style.display = "none";
}

function saveChpass()
{
    if(gId("wgOldpass").value != '' && gId("wgPass").value != '' && gId("wgRepass").value != '')
    {
        if(gId("wgPass").value == gId("wgRepass").value)
        {
            ajaxAction
			(
				new Hash(["wgOldpass", "wgPass"]),
				$basePath+"chpass",
				resulChpass
			);
            
            cancelChpass();
        }
        else
            showFlash("Las nuevas contraseñas no coinciden!");
    }
    else
        showFlash("Datos incompletos!");
}

function resulChpass(response)
{
    showFlash(response.responseText);
}

function cancelChpass()
{
    clearChpass();
    switchChpass();
}

function clearChpass()
{
    gId("wgOldpass").value = '';
    gId("wgPass").value = '';
    gId("wgRepass").value = '';
}

function validate(fields)
{
    fields = fields.split(",");
    
    flag = true;
    
    for(i = 0; i < fields.length; i++)
    {   
        gId(fields[i]).style.borderColor= "";
        gId(fields[i]).style.boxShadow= "";
        
        if(gId(fields[i]).value == '')
        {
            gId(fields[i]).style.borderColor= "#E85157";
            gId(fields[i]).style.boxShadow= "inset 0 1px 2px rgba(0,0,0,0.075),0 0 5px rgba(232,81,87,0.5)";
            
            flag = false;
        }
    }
    
    if(!flag)
        showFlash("Se requieren datos adicionales!...")
    
    return flag;
}

function clear(fields)
{
    fields = fields.split(",");
    
    for(i = 0; i < fields.length; i++)
    {   
        gId(fields[i]).style.borderColor= "";
        gId(fields[i]).style.boxShadow= "";
        
        gId(fields[i]).value = '';
    }
}

function toTable(res, cfun, mark)
{
    if(res != '_NONE_')
    {
        if(typeof mark != 'undefined')
            src = '<table id="dinamicTable_'+mark+'"> ';
        else
            src = '<table> ';
        
        rows = res.split('|:|');
        
        for(i = 0; i < rows.length; i++)
        {
            td = '';
            cells = rows[i].split('=>');
            
            for(j = 0; j < cells.length; j++)
            {
                if(j == 0)
                    td += '<td style="display:none;">'+cells[j]+'</td>';
                else
                {
                    if(cells[j].substring(0, 3) == '~|~')
                        td += '<td style="display:none;">'+cells[j].substring(3)+'</td>';
                    else
                        td += '<td>'+cells[j]+'</td>';
                }       
            }
            
            var mkcons = '';
            if(typeof mark != 'undefined')
                mkcons = ' id="'+i+'_'+mark+'"';
            
            if(typeof cfun != 'undefined')
                src += '<tr'+mkcons+' onClick="'+cfun+'(this);">'+td+'</td>';
            
            else
                src += '<tr'+mkcons+'>'+td+'</td>';
        }
        
        src += '</table>';
        
        return src;
    }
    else
        return '<table><tr><td style="display:none;">@</td><td>NO DATA</td></table>';
}

function createRows(res)
{
    var src = '';
    var rows = res.split('|:|');
    var lr = rows.length;
    
    for(var i = 0; i < lr; i++)
    {
        src += '<tr>';
        
        var cells = rows[i].split('=>');
        var lc = cells.length;
        
        for(var j = 0; j < lc; j++)
        {
            var hidden = j == 0 ? ' class="scr-hidden" ' : '';
            src += '<td'+hidden+'>'+cells[j]+'</td>';
        }
        
        src += '</tr>';
    }
    
    return src;
}

function toForm(res)
{
    if(res != '_NONE_')
    {
        res = res.split('|:|');
        
        for(i = 0; i < res.length; i++)
        {
            pts = res[i].split('=>');
            gId(pts[0]).value = pts[1];
        }
    }
}

function remConsecutive(data)
{
    var parts = data.split('_');
    
    if(parts.length > 1)
        parts.shift();
    
    return parts.join('_');
}

function getConsecutive(data)
{
    var parts = data.split('_');
    
    return parseInt(parts[0]);
}

function toHours(seconds)
{
    var hours = parseInt(seconds/3600);
    seconds = seconds%3600;
    var minuts = parseInt(seconds/60);
    seconds = parseInt(seconds%60);
    
    hours = hours < 10 ? '0'+hours : ''+hours;
    minuts = minuts < 10 ? '0'+minuts : ''+minuts;
    seconds = seconds < 10 ? '0'+seconds : ''+seconds;
    
    return hours+':'+minuts+':'+seconds;
}
