/* ####### Variables ####### */

var ctlUrl = 'uno'                                          // Url del controlador base.
var magnaFile = '';

var vMat = 0;
var vTin = 0;
var vAca = 0;
var vBas = 0;
var eAca = null;


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
    gId('cFileIn').value = this.result;
}


/* ####### Runtime ####### */

function fixValue(elem)
{
    var camp = remConsecutive(elem.id);
    
    if(camp == 'OrdMaterial')
        vMat = parseFloat(elem.cells[2].innerHTML.substring(2));
    else if(camp == 'OrdTinta')
        vTin = parseFloat(elem.cells[2].innerHTML.substring(2));
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
    var nPag = parseFloat(gId('pages').value);
    vBas = nPag*(vMat+vTin+vAca);
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
    if(validate("fileIn,xFileIn,pages,OrdMaterial,xOrdMaterial,OrdTinta,xOrdTinta,amount,unit,value"))
    {   
        var rows = gId('acabadosList').rows;
        var crows = rows.length;
        var daca = Array();
        
        for(i = 0; i < crows; i++)
            daca.push(rows[i].cells[0].innerHTML);
        
        daca = daca.join('|-|');
        daca = daca != '' ? daca : '@@@';
        
        var inotes = gId('notes').value != '' ? gId('notes').value : '@@@';
        
        var src = '<tr>';
        src += '<td class="scr-hidden">'+gId('OrdMaterial').value+'|=-=|'+gId('OrdTinta').value+'|=-=|'+gId('xFileIn').value+'|=-=|'+gId('pages').value+'|=-=|'+gId('amount').value+'|=-=|'+gId('unit').value+'|=-=|'+gId('value').value+'|=-=|'+gId('cFileIn').value+'|=-=|'+inotes+'|=-=|'+daca+'</td>';
        src += '<td><img class="picList" src="'+gId('cFileIn').value+'" /></td><td>'+gId('pages').value+'</td><td>'+gId('xOrdMaterial').value+'</td><td>'+gId('xOrdTinta').value+'</td><td>'+gId('amount').value+'</td><td>'+gId('value').value+'</td><td><img src="'+$imgPath+'rem.png" onclick="remItem(this);" title="Eliminar" /></td>';
        src += '</tr>';
        gId('itemLister').innerHTML += src;
        
        clrItem();
        calculateSub();
        gId('xFileIn').focus();
    }
}

function clrItem()
{
    clear("fileIn,xFileIn,pages,OrdMaterial,xOrdMaterial,OrdTinta,xOrdTinta,amount,unit,value,xOrdAcabado,OrdAcabado,notes");
    gId('acabadosList').innerHTML = '';
    
    magnaFile = '';
    vMat = 0;
    vTin = 0;
    vAca = 0;
    vBas = 0;
    eAca = null;
}

function remItem(elem)
{
    var rrow = elem.parentNode.parentNode;
    rrow.parentNode.removeChild(rrow);
    
    calculateSub();
}

function calculateSub()
{
    var sub = 0;
    var irows = gId('itemLister').rows;
    var lim = irows.length;
    
    for(i = 0; i < lim; i++)
        sub += parseFloat(irows[i].cells[6].innerHTML);
       
    gId('ordSubtotal').value = sub;
}

function aplicaIva()
{
    var iva = parseFloat(gId('ordIva').value)/100;
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
    
    if(gId('firmaCode').value != '')
    {
        if(validate('OrdClient,xOrdClient,ordTime,ordSubtotal,ordIva,ordTotal'))
        {
            var obser = gId('ordData').value != '' ? gId('ordData').value : '@@@';
            var dsave = gId('OrdClient').value+'|=-=|';
                dsave += gId('ordTime').value+'|=-=|';
                dsave += gId('ordSubtotal').value+'|=-=|';
                dsave += gId('ordIva').value+'|=-=|';
                dsave += gId('ordTotal').value+'|=-=|';
                dsave += gId('firmaCode').value+'|=-=|';
                dsave += obser;
            
            var ditem = Array();
            var irows = gId('itemLister').rows;
            var lim = irows.length;            
            for(i = 0; i < lim; i++)
                ditem.push(irows[i].cells[0].innerHTML);
            
            ditem = ditem.join('|:-:|');
            ditem = ditem != '' ? ditem : '@@@';
            
            dsave = dsave+'|-*-*-|'+ditem;
            
            ajaxAction
            (
                new Hash(['*param => '+dsave]),
                $basePath+'uno/save',
                okSave
            );
        }
    }
    else
        showFlash('El cliente debe firmar la orden antes de registrarla!');
}

function okSave(response)
{
    alert(response.responseText)
}