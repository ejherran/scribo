/* ####### Variables ####### */

var ctlUrl = 'dos'                                          // Url del controlador base.
var magnaFile = '';

var vMat = 0;
var rMat = '';
var vTin = 0;
var rTin = '';
var vAca = 0;
var vBas = 0;
var eAca = null;

var ctItem = 0;

/* Transferencia de datos */
var tmpFile = '';
var firmaCode = '';
var upLimit = '';
var transfer = new Hash([]);
var transferId = -1;

/* Acumulador Global */

var dSave = '';

/* ####### Init Global ####### */

function $_init()
{
    iniCanvas();
    
    gId('xxFileIn').onclick = falseFile;
    gId('xFileIn').onkeypress = falseFile;
    gId('fileIn').onchange = inFile;
    
    gId('xOrdClient').onkeydown = FindHelp;
    gId('xOrdMaterial').onkeydown = FindHelp;
    gId('xOrdMaterial').secondAction = fixValue;
    gId('xOrdTinta').onkeydown = FindHelp;
    gId('xOrdTinta').secondAction = fixValue;
    gId('xOrdAcabado').onkeydown = FindHelp;
    gId('xOrdAcabado').secondAction = fixValue;
    gId('kOrdAcabado').onclick = addAcabado;
    
    gId('unit').onfocus = calculateBase;
    gId('unit').onblur = validateBase;
    gId('value').onfocus = calculateValor;
    
    gId('add').onclick = addItem;
    gId('clr').onclick = clrItem;
    gId('ordTotal').onfocus = aplicaIva;
    
    gId('save').onclick = saveOrder;
    
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
        gId('save').style.display = 'inline-block';
}

/* ####### Archivos ####### */

function falseFile(event)
{
    var flag = event.type == "keypress" && event.keyCode == 13 ? true : false;
    flag = flag || event.type == "click" ? true: false;
    
    if(flag)
        falseClick('fileIn');
}

function inFile(event)
{
    magnaFile = event.target.files[0];
    gId('xFileIn').value = magnaFile.name;
    
    gId('xFileIn').focus();
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
    tmpFile = this.result;
}


/* ####### Runtime ####### */

function fixValue(elem)
{
    var camp = remConsecutive(elem.id);
    
    if(camp == 'OrdMaterial')
    {
        vMat = parseFloat(elem.cells[2].innerHTML.substring(2));
        rMat = elem.cells[3].innerHTML;
    }
    else if(camp == 'OrdTinta')
    {
        vTin = parseFloat(elem.cells[2].innerHTML.substring(2));
        rTin = elem.cells[3].innerHTML;
    }
    else if(camp == 'OrdAcabado')
        eAca = elem;
}

function addAcabado()
{
    if(eAca != null)
    {
        gId('acabadosList').innerHTML += '<tr><td class="scr-hidden">'+eAca.cells[0].innerHTML+'</td><td class="scr-hidden">'+eAca.cells[2].innerHTML.substring(2)+'</td><td>'+eAca.cells[1].innerHTML+'</td><th><img src="'+$imgPath+'rem.png" onclick="remAcabado(this);" title="Eliminar" /></th></tr>';
        gId('OrdAcabado').value = '';
        gId('xOrdAcabado').value = '';
    
        gId('xOrdAcabado').focus();
        eAca = null;
        
        calculateAcabado();
    }
    else
        showFlash('Debe seleccionar un acabado!...');
}

function remAcabado(elem)
{
    var rrow = elem.parentNode.parentNode;
    rrow.parentNode.removeChild(rrow);
    
    calculateAcabado();
}

function calculateAcabado()
{
    var rows = gId('acabadosList').rows;
    var crows = rows.length;
    vAca = 0;
    
    for(i = 0; i < crows; i++)
        vAca += parseFloat(rows[i].cells[1].innerHTML);
}

function calculateBase()
{
    var nIte = parseFloat(gId('amount').value);
    
    var dW = (parseFloat(gId('width').value)/100);
    var dH = (parseFloat(gId('height').value)/100);
    var dMen = dW*dH;
    dMen = Math.round(dMen * Math.pow(10,2))/Math.pow(10,2);
    
    var tvMat = disRange(rMat, dMen*nIte)*vMat;
    var tvTin = disRange(rTin, dMen*nIte)*vTin;
    
    vBas = dMen*(tvMat+tvTin+vAca);
    vBas = Math.round(vBas * Math.pow(10,2))/Math.pow(10,2);
    vBas = parseFloat(1.0*vBas.toFixed(2));
    
    gId('unit').value = vBas;
}

function validateBase()
{
    var vActual = parseFloat(gId("unit").value);
    
    if( !isNaN(vActual) && vActual < vBas )
    {
        gId('unit').value = vBas;
        showFlash("El valor indicado es inferior al mínimo aceptable!");
    }
}

function calculateValor()
{
    gId('value').value = parseFloat(gId('unit').value)*parseFloat(gId('amount').value);
}

function addItem()
{
    if(validate("fileIn,xFileIn,width,height,OrdMaterial,xOrdMaterial,OrdTinta,xOrdTinta,amount,unit,value"))
    {  
        calculateAcabado();
        calculateBase();
        calculateValor();
        
        var rows = gId('acabadosList').rows;
        var crows = rows.length;
        var daca = Array();
        
        for(i = 0; i < crows; i++)
            daca.push(rows[i].cells[0].innerHTML);
        
        daca = daca.join(';');
        daca = daca != '' ? daca : '@';
        
        var inotes = gId('notes').value != '' ? gId('notes').value : '@';
        var iexpiry = gId('expiry').value != '' ? gId('expiry').value : '@';
        
        ctItem += 1;
        
        transfer.put('itm'+ctItem+' => '+tmpFile);
        tmpFile = '';
        
        var src = '<tr id="itm'+ctItem+'">';
        src += '<td class="scr-hidden">'+gId('OrdMaterial').value+'</td>';
        src += '<td class="scr-hidden">'+gId('OrdTinta').value+'</td>';
        src += '<td style="width: 29%;">'+gId('xFileIn').value+'</td>';
        src += '<td style="width: 10%;">'+gId('width').value+'</td>';
        src += '<td style="width: 10%;">'+gId('height').value+'</td>';
        src += '<td style="width: 10%;">'+gId('amount').value+'</td>';
        src += '<td style="width: 10%;">'+gId('unit').value+'</td>';
        src += '<td style="width: 10%;">'+gId('value').value+'</td>';
        src += '<td class="scr-hidden">STORAGE</td>';
        src += '<td class="scr-hidden">SIGNATURE</td>';
        src += '<td class="scr-hidden">'+iexpiry+'</td>';
        src += '<td class="scr-hidden">'+inotes+'</td>';
        src += '<td class="scr-hidden">'+daca+'</td>';
        src += '<td style="width: 10%;">'+gId('xOrdMaterial').value+'</td>';
        src += '<td style="width: 10%;">'+gId('xOrdTinta').value+'</td>';
        src += '<td style="width: 1%;"><img src="'+$imgPath+'rem.png" onclick="remItem(this);" title="Eliminar" /></td>';
        src += '</tr>';
        gId('itemLister').innerHTML += src;
        
        clrItem();
        calculateSub();
        gId('xFileIn').focus();
    }
}

function clrItem()
{
    clear("fileIn,xFileIn,width,height,OrdMaterial,xOrdMaterial,OrdTinta,xOrdTinta,amount,unit,value,xOrdAcabado,OrdAcabado,notes,expiry");
    gId('acabadosList').innerHTML = '';
    
    magnaFile = '';
    vMat = 0;
    rMat = 0;
    vTin = 0;
    rTin = 0;
    vAca = 0;
    vBas = 0;
    eAca = null;
}

function remItem(elem)
{
    var rrow = elem.parentNode.parentNode;
    rrow.parentNode.removeChild(rrow);
    
    transfer.pop(rrow.id);
    
    calculateSub();
}

function calculateSub()
{
    var sub = 0;
    var irows = gId('itemLister').rows;
    var lim = irows.length;
    
    for(i = 0; i < lim; i++)
        sub += parseFloat(irows[i].cells[7].innerHTML);
       
    gId('ordSubtotal').value = sub;
}

function aplicaIva()
{
    var iva = '';
    
    if(gId('ordIva').value == '')
        gId('ordIva').value = '16';
    
    iva = parseFloat(gId('ordIva').value)/100;
    if(!isNaN(iva))
        gId('ordTotal').value = ((iva+1)*parseFloat(gId('ordSubtotal').value)).toFixed(2);
    else
    {
        gId('ordIva').value = '';
        gId('ordIva').focus();
    }
        
}

function saveOrder()
{
    
    if(validate('OrdClient,xOrdClient,ordTime,ordSubtotal,ordIva,ordTotal'))
    {
        if(transfer.len > 0)
        {
            if(firmaCode != '')
            {
                var obser = gId('ordData').value != '' ? gId('ordData').value : '@';
                
                dSave = gId('OrdProc').value+'|-|';
                dSave += gId('OrdClient').value+'|-|';
                dSave += gId('ordTime').value+'|-|';
                dSave += gId('ordSubtotal').value+'|-|';
                dSave += gId('ordIva').value+'|-|';
                dSave += gId('ordTotal').value+'|-|';
                dSave += obser;
                
                transferId = 0;
                uploader();
            }
            else
                showFlash('El cliente debe firmar la orden antes de registrarla!');
        }
        else
            showFlash('Es necesario definir un ítem para registrar la orden!.');
    }
}

function proSave()
{
    var rows = gId('itemLister').rows;
    var crows = rows.length;
    var dite = Array();
    
    for(i = 0; i < crows; i++)
    {
        var tite = [rows[i].cells[0].innerHTML, rows[i].cells[1].innerHTML, rows[i].cells[2].innerHTML, rows[i].cells[3].innerHTML, rows[i].cells[4].innerHTML, rows[i].cells[5].innerHTML, rows[i].cells[6].innerHTML, rows[i].cells[7].innerHTML, rows[i].cells[8].innerHTML, rows[i].cells[9].innerHTML, rows[i].cells[10].innerHTML, rows[i].cells[11].innerHTML, rows[i].cells[12].innerHTML];
        dite.push(tite.join('|-|'));
    }
    
    dite = dite.join('|:|');
    
    dSave = dSave+'|:|'+dite+'|:|'+firmaCode;
    sendSave();
}

function sendSave()
{
    ajaxAction
    (
        new Hash(['*param => '+dSave]),
        $basePath+"dos/save",
        okSave
    );
}

function okSave(response)
{
    if(parseInt(response.responseText) > 0)
        document.location = $basePath+"dos/"+response.responseText+"/visor";
    else
        showFlash("Imposible procesar la orden!.");
}

function uploader()
{
    if(transferId < transfer.len && transferId > -1)
    {
        var tK = transfer.listKeys()[transferId];
        
        upUrl = $storage+'/scribo/repository.php';
        upTime = new Date().getTime();
        upName = gId(tK).cells[2].innerHTML;
        upOut = '@';
        upData = transfer.getValue(tK);
        upLimit = upData.length;
        transfer.fixV(tK, '');
        upAction = okUp;
        
        gId('upLabel').innerHTML = upName;
        
        showB('uploader');
        
        partialUpload('');
    }
    else
    {
        hide('uploader');
        proSave();
    }
}

function okUp()
{
    var porcen = parseInt((1-(upData.length / upLimit))*100);
    gId('upBar').style.width = porcen+'%';
    
    if(upHash != '')
    {
        var tK = transfer.listKeys()[transferId];
        gId(tK).cells[8].innerHTML = upOut;
        gId(tK).cells[9].innerHTML = upHash;
        partialClear();
        transferId += 1;
        uploader();
    }
}

/* ####### Rangos ####### */

function disRange(rango, cantidad)
{
    cantidad = parseFloat(cantidad);
    var fac = 1;
    
    rango = rango.split(';');
    for(var i = 0; i < rango.length; i++)
    {
        var tmp = rango[i].split(',');
        if(tmp.length == 3)
        {
            var min = parseFloat(tmp[0]);
            var max = parseFloat(tmp[1]);
            var por = 1.0 - (parseFloat(tmp[2])/100.0);
            
            if(min <= cantidad && cantidad < max)
            {
                fac = por;
                break;
            }
        }
    }
    
    return fac;
}
