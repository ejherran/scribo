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

function toTable(res, cfun)
{
    if(res != '_NONE_')
    {
        src = "<table> ";
        
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
                    td += '<td>'+cells[j]+'</td>';
            }
            
            if(typeof cfun != 'undefined')
                src += '<tr onClick="'+cfun+'(this);">'+td+'</td>';
            else
                src += '<tr>'+td+'</td>';
        }
        
        src += "</table>";
        
        return src;
    }
    else
        return '<table><tr><td style="display:none;">@</td><td>NO DATA</td></table>';
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
