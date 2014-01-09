var srcLoader = '';
var idxRole = -1;
var idxPers = -1;
var blrPers = true;

function $_init()
{
    gId("krole").onclick = listRole;
    gId("xrole").onkeydown = nextRole;
    gId("lrole").onclick = picRole;
    
    gId("kpersonal_id").onclick = exPersonal;
    gId("xpersonal_id").onkeydown = exPersonal;
    gId("xpersonal_id").onblur = clPersonal;
    gId("lpersonal_id").onclick = picPersonal;
    gId("lpersonal_id").onmouseover = switchBlrPers;
    gId("lpersonal_id").onmouseout = switchBlrPers;
    
    gId("btnClear").onclick = clrUsuario;
    gId("btnSave").onclick = savUsuario;
    gId("btnDelete").onclick = delUsuario;
    
    srcLoader = gId("zone").innerHTML;
    
    listUsuario();
}

function listRole()
{
    switchView("lrole");
}

function nextRole(event)
{   
    hide("lrole");
    
    flag = true;
    
    if(event.keyCode == 38 || event.keyCode == 40)
    {
        flag = false;
        
        if(event.keyCode == 38)
            idxRole -= 1;
        else if(event.keyCode == 40)
            idxRole += 1;
        
        rows = gId("lrole").getElementsByTagName('table')[0].rows;
        lim = rows.length;
        
        idxRole = idxRole < 1 ? 1 : idxRole;
        idxRole = idxRole >= lim ? lim-1 : idxRole;
        
        gId('role').value = rows[idxRole].cells[0].innerHTML;
        gId('xrole').value = rows[idxRole].cells[1].innerHTML;
    }
    else if(event.keyCode == 8)
    {
        idxRole = -1;
        gId('role').value = '';
        gId('xrole').value = '';
    }
    
    return flag;
}

function picRole(event)
{
    hide("lrole");
    idxRole = -1;
    blrPers = true;
    gId('xrole').focus();
    
    row = event.target.parentNode;
    
    if(row.cells[0].innerHTML != '@')
    {
        gId('role').value = row.cells[0].innerHTML;
        gId('xrole').value = row.cells[1].innerHTML;
    }
    else
    {
        gId('role').value = '';
        gId('xrole').value = '';
    }
}

function switchBlrPers()
{
    blrPers = blrPers ? false : true;
}

function exPersonal(event)
{
    code = event.type == 'click' ? 'click' : event.keyCode;
    
    flag = true;
    
    if(code == 38 || code == 40)
    {
        flag = false;
        
        if(code == 38)
            idxPers -= 1;
        else if(code == 40)
            idxPers += 1;
        
        tab = gId("lpersonal_id").getElementsByTagName('table')[0];
        if(typeof tab != 'undefined')
        {
            rows = tab.rows;
            lim = rows.length;
        }
        else
            lim = 0;
        
        if(lim > 0)
        {
            idxPers = idxPers < 0 ? 0 : idxPers;
            idxPers = idxPers >= lim ? lim-1 : idxPers;
            
            if(rows[idxPers].cells[0].innerHTML != '@')
            {
                gId('personal_id').value = rows[idxPers].cells[0].innerHTML;
                gId('xpersonal_id').value = rows[idxPers].cells[1].innerHTML+' - '+rows[idxPers].cells[2].innerHTML+' '+rows[idxPers].cells[3].innerHTML;
            }
        }
    }
    else if(code == 8)
    {
        idxPers = -1;
        gId('personal_id').value = '';
        gId('xpersonal_id').value = '';
    }
    else if(code == 13 || code == 'click')
    {
        ajaxAction
        (
            new Hash(["param => xpersonal_id"]),
            $basePath+"pers/find",
            xexPersonal
        );
    }
    
    return flag;
}

function xexPersonal(response)
{
    idxPers = -1;
    gId("lpersonal_id").innerHTML = toTable(response.responseText);
    show("lpersonal_id");
}

function picPersonal()
{
    hide("lpersonal_id");
    idxPers = -1;
    gId('xpersonal_id').focus();
    
    row = event.target.parentNode;
    
    if(row.cells[0].innerHTML != '@')
    {
        gId('personal_id').value = row.cells[0].innerHTML;
        gId('xpersonal_id').value = row.cells[1].innerHTML+' - '+row.cells[2].innerHTML+' '+row.cells[3].innerHTML;
    }
    else
    {
        gId('personal_id').value = '';
        gId('xpersonal_id').value = '';
    }
}

function clPersonal(event)
{
    if(blrPers)
    {
        idxPers = -1;
        hide("lpersonal_id");
    }
}

function clrUsuario()
{
    clear("id,personal_id,xpersonal_id,user,pass,role,xrole,data");
}

function savUsuario()
{
    if(validate("personal_id,xpersonal_id,user,pass,role,xrole"))
    {
        ajaxAction
        (
            new Hash(["id","personal_id","xpersonal_id","user","pass","role","xrole","data"]),
            $basePath+"usua/save",
            xSavUsuario
        );
    }
}

function xSavUsuario(response)
{
    showFlash(response.responseText);
    listUsuario();
}

function delUsuario()
{
    if(validate("id"))
    {
        if(confirm("Desea eliminar este registro?"))
        {
            ajaxAction
            (
                new Hash(["id"]),
                $basePath+"usua/del",
                xDelUsuario
            );
        }
    }
}

function xDelUsuario(response)
{
    showFlash(response.responseText);
    listUsuario();
}

function listUsuario()
{
    ajaxAction
    (
        new Hash(["*tok => *"]),
        $basePath+"usua/enum",
        xListUsuario
    );
    
    showLoader();
}

function xListUsuario(response)
{
    src = toTable(response.responseText, 'getUsuario');
    clrUsuario();
    idTim = setTimeout(function(){ gId("zone").innerHTML = src; clearTimeout(idTim); }, 250);
}

function getUsuario(elem)
{
    id = elem.cells[0].innerHTML;
    
    ajaxAction
    (
        new Hash(["*id => "+id]),
        $basePath+"usua/get",
        xGetUsuario
    );
}

function xGetUsuario(response)
{
    toForm(response.responseText);
}

function showLoader()
{
    gId("zone").innerHTML = srcLoader;
}
