var srcLoader = '';
var idxType = -1;

function $_init()
{
    srcLoader = gId("zone").innerHTML;
    
    gId("ktype").onclick = listType;
    gId("xtype").onkeydown = nextType;
    gId("ltype").onclick = picType;
    
    gId("btnClear").onclick = clrTinta;
    gId("btnSave").onclick = savTinta;
    gId("btnDelete").onclick = delTinta;
    
    gId("disAdd").onclick = addDesc;
    
    listTinta();
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

function clrTinta()
{
    clear("id,name,cost,value,type,xtype,data,disMin,disMax,disPor");
    gId('tabDisZone').innerHTML = '<tr style="background: #44729E; color: #fff;"><td>MIN</td><td>MAX</td><td>DESC (%)</td><td style="width: 20px;">&nbsp;</td></tr>';
}

function savTinta()
{
    if(validate("name,cost,value,type,xtype"))
    {
        ajaxAction
        (
            new Hash(["id","name","cost","value","type","xtype","discount","data"]),
            $basePath+"tint/save",
            xSavTinta
        );
    }
}

function xSavTinta(response)
{
    showFlash(response.responseText);
    listTinta();
}

function delTinta()
{
    if(validate("id"))
    {
        if(confirm("Desea eliminar este registro?"))
        {
            ajaxAction
            (
                new Hash(["id"]),
                $basePath+"tint/del",
                xDelTinta
            );
        }
    }
}

function xDelTinta(response)
{
    showFlash(response.responseText);
    listTinta();
}


function listTinta()
{
    ajaxAction
    (
        new Hash(["*tok => *"]),
        $basePath+"tint/enum",
        xListTinta
    );
    
    showLoader();
}

function xListTinta(response)
{
    src = toTable(response.responseText, 'getTinta');
    clrTinta();
    idTim = setTimeout(function(){ gId("zone").innerHTML = src; clearTimeout(idTim); }, 250);
}

function getTinta(elem)
{
    id = elem.cells[0].innerHTML;
    
    ajaxAction
    (
        new Hash(["*id => "+id]),
        $basePath+"tint/get",
        xGetTinta
    );
}

function xGetTinta(response)
{
    clrTinta();
    toForm(response.responseText);
    
    var diss = gId('discount').value;
    diss = diss.split(';');
    
    for(var i = 0; i < diss.length; i++)
    {
        var tmp = diss[i].split(',');
        if(tmp.length == 3)
            gId('tabDisZone').innerHTML += '<tr><td>'+tmp[0]+'</td><td>'+tmp[1]+'</td><td>'+tmp[2]+'</td><td><img style="height: 75%; vertical-align: bottom;"src="'+$imgPath+'/rem.png" onclick="elimDesc(this);" title="Eliminar!." /></td></tr>';
    }
}

function elimDesc(elem)
{
    var erow = elem.parentNode.parentNode;
    erow.parentNode.removeChild(erow);
    disTabText();
}

function addDesc()
{
    if(validate("disMin,disMax,disPor"))
    {
        gId('tabDisZone').innerHTML += '<tr><td>'+gId('disMin').value+'</td><td>'+gId('disMax').value+'</td><td>'+gId('disPor').value+'</td><td><img style="height: 75%; vertical-align: bottom;"src="'+$imgPath+'/rem.png" onclick="elimDesc(this);" title="Eliminar!." /></td></tr>';
        clear('disMin,disMax,disPor');
        disTabText();
    }
    
    gId('disMin').focus();
}

function disTabText()
{
    var drows = gId('tabDisZone').rows;
    var txt = '';
    for(var i = 1; i < drows.length; i++)
    {
        txt += drows[i].cells[0].innerHTML+','+drows[i].cells[1].innerHTML+','+drows[i].cells[2].innerHTML+';';
    }
    
    gId('discount').value = txt;
}

