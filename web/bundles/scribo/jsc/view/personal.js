var srcLoader = '';

function $_init()
{
    srcLoader = gId("zone").innerHTML;
    
    gId("btnClear").onclick = clrPersonal;
    gId("btnSave").onclick = savPersonal;
    gId("btnDelete").onclick = delPersonal;
    
    listPersonal();
}

function showLoader()
{
    gId("zone").innerHTML = srcLoader;
}

function clrPersonal()
{
    clear("id,document,surname,name,address,phone,mail,data");
}

function savPersonal()
{
    if(validate("document,surname,name,address,phone,mail"))
    {
        ajaxAction
        (
            new Hash(["id","document","surname","name","address","phone","mail","data"]),
            $basePath+"pers/save",
            xSavPersonal
        );
    }
}

function xSavPersonal(response)
{
    showFlash(response.responseText);
    listPersonal();
}

function delPersonal()
{
    if(validate("id"))
    {
        if(confirm("Desea eliminar este registro?"))
        {
            ajaxAction
            (
                new Hash(["id"]),
                $basePath+"pers/del",
                xDelPersonal
            );
        }
    }
}

function xDelPersonal(response)
{
    showFlash(response.responseText);
    listPersonal();
}

function listPersonal()
{
    ajaxAction
    (
        new Hash(["*tok => *"]),
        $basePath+"pers/enum",
        xListPersonal
    );
    
    showLoader();
}

function xListPersonal(response)
{
    src = toTable(response.responseText, 'getPersonal');
    clrPersonal();
    idTim = setTimeout(function(){ gId("zone").innerHTML = src; clearTimeout(idTim); }, 250);
}

function getPersonal(elem)
{
    id = elem.cells[0].innerHTML;
    
    ajaxAction
    (
        new Hash(["*id => "+id]),
        $basePath+"pers/get",
        xGetPersonal
    );
}

function xGetPersonal(response)
{
    toForm(response.responseText);
}
