var srcLoader = '';
var idxType = -1;
var magnaFile = '';

function $_init()
{
    gId("bLogo").onclick = falseFile;
    gId('logo').onchange = inFile;
    
    gId("ktype").onclick = listType;
    gId("xtype").onkeydown = nextType;
    gId("ltype").onclick = picType;
    
    gId("btnTest").onclick = testStorage;
    gId("btnSave").onclick = savConfig;
}

function falseFile(event)
{
    falseClick('logo');
}

function inFile(event)
{
    magnaFile = event.target.files[0];
    loadFile();
}

function loadFile()
{
    var reader = new FileReader();
    reader.onload = createCode;
    reader.readAsDataURL(magnaFile);
}

function createCode(event)
{
    var tmpFile = this.result;
    if(tmpFile.split(',')[0] != 'data:image/jpeg;base64')
        showFlash("El logo debe ser una imagen en formato JPG/JPEG!.");
    else
    {
        if(tmpFile.length > 2097152)
            showFlash("El logo seleccionado es muy pesado, Max 2MB!.");
        else
            gId('vLogo').src = tmpFile;
    }
}

function testStorage()
{
    ajaxTest
    (
        new Hash(['*action => test']),
        gId('storage').value+'/scribo/repository.php',
        resStorage
    );
}

function resStorage(response)
{
    if(response.status != 200 || response.responseText != 'Ok!')
        showFlash("Imposible conectar con el servidor de almacenamiento local!");
    else if(response.responseText == 'Ok!')
        showFlash("Servidor de almacenamiento local, conexión exitosa!");
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

function savConfig()
{
    if(validate("type,xtype,document,name,contact,address,phone,mail"))
    {
        ajaxAction
        (
            new Hash(["type","document","name","contact","address","phone","web","mail","storage","report","*logo => "+gId('vLogo').src]),
            $basePath+"conf/save",
            xSavConfig
        );
    }
}

function xSavConfig(response)
{
    showFlash(response.responseText);
}


