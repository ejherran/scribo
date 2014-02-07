var srcLoader = '';
var idxType = -1;

function $_init()
{
    gId("ktype").onclick = listType;
    gId("xtype").onkeydown = nextType;
    gId("ltype").onclick = picType;
    
    gId("btnClear").onclick = clrCliente;
    gId("btnSave").onclick = savCliente;
    gId("btnDelete").onclick = delCliente;
    
    listCliente();
    
    srcLoader = gId("zone").innerHTML;
}

function listType()
{
    switchView("ltype");
}

function nextType(event)
{   
    hide("ltype");
    
    flag = true;
    
    if(event.keyCode == 38 || event.keyCode == 40)
    {
        flag = false;
        
        if(event.keyCode == 38)
            idxType -= 1;
        else if(event.keyCode == 40)
            idxType += 1;
        
        rows = gId("ltype").getElementsByTagName('table')[0].rows;
        lim = rows.length;
        
        idxType = idxType < 1 ? 1 : idxType;
        idxType = idxType >= lim ? lim-1 : idxType;
        
        gId('type').value = rows[idxType].cells[0].innerHTML;
        gId('xtype').value = rows[idxType].cells[1].innerHTML;
    }
    else if(event.keyCode == 8)
    {
        idxRole = -1;
        gId('type').value = '';
        gId('xtype').value = '';
    }
    
    return flag;
}

function picType(event)
{
    hide("ltype");
    idxRole = -1;
    blrPers = true;
    gId('xtype').focus();
    
    row = event.target.parentNode;
    
    if(row.cells[0].innerHTML != '@')
    {
        gId('type').value = row.cells[0].innerHTML;
        gId('xtype').value = row.cells[1].innerHTML;
    }
    else
    {
        gId('type').value = '';
        gId('xtype').value = '';
    }
}

function showLoader()
{
    gId("zone").innerHTML = srcLoader;
}

function clrCliente()
{
    clear("id,type,xtype,document,name,contact,address,phone,mail,data");
}

function savCliente()
{
    if(validate("type,xtype,document,name,contact,address,phone,mail"))
    {
        ajaxAction
        (
            new Hash(["id","type","document","name","contact","address","phone","mail","data"]),
            $basePath+"clie/save",
            xSavCliente
        );
    }
}

function xSavCliente(response)
{
    showFlash(response.responseText);
    listCliente();
}

function delCliente()
{
    if(validate("id"))
    {
        if(confirm("Desea eliminar este registro?"))
        {
            ajaxAction
            (
                new Hash(["id"]),
                $basePath+"clie/del",
                xDelCliente
            );
        }
    }
}

function xDelCliente(response)
{
    showFlash(response.responseText);
    listCliente();
}


function listCliente()
{
    ajaxAction
    (
        new Hash(["*tok => *"]),
        $basePath+"clie/enum",
        xListCliente
    );
    
    showLoader();
}

function xListCliente(response)
{
    src = toTable(response.responseText, 'getCliente');
    clrCliente();
    idTim = setTimeout(function(){ gId("zone").innerHTML = src; clearTimeout(idTim); }, 250);
}

function getCliente(elem)
{
    id = elem.cells[0].innerHTML;
    
    ajaxAction
    (
        new Hash(["*id => "+id]),
        $basePath+"clie/get",
        xGetCliente
    );
}

function xGetCliente(response)
{
    toForm(response.responseText);
}

