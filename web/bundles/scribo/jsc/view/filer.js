var iSel = '';

var srcLoader = '';
var cabs = '';

var FOK = false;

function $_init()
{
    srcLoader = '<tr><th colspan="7" style="background: #fff; height: 345px;"><img src="'+$imgPath+'/loader.gif" /></th></tr>';
    cabs = '<tr><th><input id="checker" type="checkbox" onclick="setCheck();" /></th><th>ORDEN</th><th>CLIENTE</th><th>NOMBRE ORIGINAL</th><th>FECHA DE CREACIÓN</th><th>FECHA DE EXPIRACIÓN</th><th><img src="'+$imgPath+'/refresh.png" onclick="getList();" title="Atualizar..." /></th></tr>';
    
    gId('purge').onclick = purgeAll;
    gId('update').onclick = updater;
    gId('delete').onclick = deleter;
    
    ajaxTest
    (
        new Hash(['*action => test']),
        $storage+'/scribo/repository.php',
        testStorage
    );
}

function testStorage(response)
{
    if(response.status != 200 || response.responseText != 'Ok!')
        showFlash("Imposible conectar con el servidor de almacenamiento local!");
    else if(response.responseText == 'Ok!')
    {
        gId('control').style.display = 'table';
        FOK = true;
        getList();
    }
}

function showLoader()
{
    gId("flist").innerHTML = cabs+srcLoader;
}

function getList()
{
    if(FOK)
    {
        showLoader();
        
        ajaxAction
        (
            new Hash(['*param => *']),
            $basePath+"filer/list",
            refresh
        );
    }
}

function listSelect()
{
    var tmp = new Array();
    
    var rows = gId('flist').rows;
    var limit = rows.length;
    
    for(var i = 1; i < limit; i++)
    {
        if(rows[i].cells[0].firstChild.checked)
            tmp.push(rows[i].cells[1].innerHTML);
    }
    
    iSel = tmp.join(',');
}

function setCheck()
{
    if(gId('checker').checked)
        cheackAll();
    else
        unCheackAll();
}

function cheackAll()
{
    var rows = gId('flist').rows;
    var limit = rows.length;
    
    for(var i = 1; i < limit; i++)
    {
        rows[i].cells[0].firstChild.checked = true;
    } 
}

function unCheackAll()
{
    var rows = gId('flist').rows;
    var limit = rows.length;
    
    for(var i = 1; i < limit; i++)
    {
        rows[i].cells[0].firstChild.checked = false;
    } 
}

function purgeAll()
{
    if(confirm('Esto eliminara definitivamente los archivos "EXPIRADOS" del almacenamiento local. Desea continuar?'))
    {
        ajaxAction
        (
            new Hash(['*param => *']),
            $basePath+"filer/purge",
            localPurge
        );
    }
}

function localPurge(response)
{
    ajaxAction
    (
        new Hash(['*action => delete', '*target => '+response.responseText]),
        $storage+'/scribo/repository.php',
        getList
    );
}

function updater()
{
    listSelect();
    
    if(iSel != '' && gId('date').value != '')
    {
        ajaxAction
        (
            new Hash(['*param => '+iSel, '*expiry => '+gId('date').value]),
            $basePath+"filer/update",
            getList
        );
    }
    else
        showFlash("Debe indicar una nueva fecha de expiración y seleccionar los archivos objetivo!.");
}

function deleter()
{
    listSelect();
    
    if(iSel != '')
    {
        if(confirm('Esto eliminara definitivamente los archivos seleccionados del almacenamiento local. Desea continuar?'))
        ajaxAction
        (
            new Hash(['*param => '+iSel]),
            $basePath+"filer/delete",
            localPurge
        );
    }
    else
        showFlash("Debe seleccionar los archivos objetivo!.");
}

function refresh(response)
{
    if(response.responseText)
    {
        var src = '';
        var rows = response.responseText.split('|:|');
        var lr = rows.length;
        
        for(var i = 0; i < lr; i++)
        {
            src += '<tr>';
            
            var cells = rows[i].split('=>');
            var lc = cells.length;
            
            var festa = '';
            if(cells[7] == 'EXPIRED')
                festa = '<b style="color: red;">EXPIRED</b>';
            else if(cells[7] == '@')
                festa = '<b style="color: blue;">TO DELIVER</b>';
            else
            {
                if(cells[8] == 'W')
                    festa = festa = '<b style="color: orange;" title="Waiting for delivery...">'+cells[7]+'</b>';
                else
                    festa = cells[7];
            }
            
            src += '<td><input type="checkbox" /></td>';
            src += '<td class="scr-hidden">'+cells[0]+'_'+cells[1]+'</td>';
            src += '<td>'+cells[2]+'</td>';
            src += '<td>'+cells[3]+'</td>';
            src += '<td>'+cells[4]+'</td>';
            src += '<td>'+cells[6].split(' ')[0]+'</td>';
            src += '<td>'+festa+'</td>';
            src += '<td><a href="'+$storage+'/scribo/files/'+cells[5]+'" target="_blank"></a></td>';
            
            src += '</tr>';
        }
        
        idTim = setTimeout(function(){ gId('flist').innerHTML = cabs+src; clearTimeout(idTim); }, 250);
    }
    else
    {
        idTim = setTimeout(function(){ gId('flist').innerHTML = cabs+'<tr><td colspan="7">NO DATA</td></tr>'; clearTimeout(idTim); }, 250);
    }
}

function flClear()
{
    gId('date').value = '';
    iSel = '';
}
