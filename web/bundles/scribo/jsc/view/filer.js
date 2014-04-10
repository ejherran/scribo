var iSel = '';

function $_init()
{
    gId('checker').onchange = setCheck;
    gId('purge').onclick = purgeAll;
    gId('update').onclick = updater;
    gId('delete').onclick = deleter;
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
    if(confirm('Esto eliminara definitivamente los archivos "EXPIRADOS" del almacenamiento local. Desea continuar? '))
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
        refresh
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
            refresh
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
    document.location.reload();
}
