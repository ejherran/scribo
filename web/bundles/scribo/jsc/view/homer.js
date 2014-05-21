var ctlUrl = 'home'                                          // Url del controlador base.

var uDek = null;

var pId = '';
var oId = '';
var firmaCode = '';

var srcLoader = '';
var cabs = '';

/* --- Actualizar Archivo --- */
var dId = '';
var oType = '';
var tmpFile = '';
var idActu = '';
var oldFile = '';
var oldName = '';
var oldSig = '';
var magnaFile = null;

/* --- Calacula perdidad ---*/
var matIt = new Array();
var vvPer = 0;

function $_init()
{
    srcLoader = '<tr><th colspan="6" style="background: #fff; height: 345px;"><img src="'+$imgPath+'/loader.gif" /></th></tr>';
    cabs = '<tr><th>ORDEN</th><th>TIPO</th><th>CLIENTE</th><th>F. DE REGISTRO</th><th>T. PARA ENTREGA</th><th><img src="'+$imgPath+'/refresh.png" onclick="getList();" title="Atualizar..." /></th></tr>';
    
    gId('lOrder').onclick = fixPid;
    gId('cicle').onclick = getCicle;
    gId('closer').onclick = closeCicle;
    gId('dCloser').onclick = closeDetaller;
    gId('detCloser').onclick = closeDet;
    gId('lgCloser').onclick = closeLog;
    gId('flOk').onclick = actFlot;
    gId('flCn').onclick = actFlot;
    
    if(gId('ctFirma') != null)
        iniCanvas();
    
    if(gId('proce') != null)
        gId('proce').onclick = procesar;
    
    if(gId('xreciv') != null)
    {
        gId('xreciv').onkeydown = FindHelp;
        gId('xreciv').beforeAction = prevCon;
    }
    
    if(gId('action') != null)
        gId('action').onchange = sameUser;
    
    if(gId('entrega') != null)
        gId('entrega').onclick = verifyEnt;
    
    if(gId('btnCancel') != null)
        gId('btnCancel').onclick = cancelOrder;
    
    if(gId('btnPerdida') != null)
        gId('btnPerdida').onclick = viewPerdida;
    
    if(gId('perCancel') != null)
        gId('perCancel').onclick = closePerdida;
    
    if(gId('perClear') != null)
        gId('perClear').onclick = clearPerdida;
    
    if(gId('perApply') != null)
        gId('perApply').onclick = applyPerdida;
    
    if(gId('perAdd') != null)
        gId('perAdd').onclick = addPerdida;
        
    if($jar == 'A')
    {
        gId('fileIn').onchange = inFile;
    }
    
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
        getList();
    }
}

function showLoader()
{
    gId("lOrder").innerHTML = cabs+srcLoader;
}

function getList()
{
    showLoader();
    
    ajaxAction
    (
        new Hash(['*param => *']),
        $basePath+"home/list",
        refresh
    );
}

function fixPid(event)
{
    var obj = event.target;
    
    if((obj.nodeName == 'TD' || obj.nodeName == 'B') && obj.firstChild.nodeName != 'IMG')
    {
        if(obj.nodeName == 'B')
            obj = obj.parentNode;
        
        var rows = obj.parentNode.parentNode.rows;
        var limit = rows.length;
        for(var i = 1; i < limit; i++)
        {
            rows[i].style.background = '';
            rows[i].style.color = '';
        }
        
        var tmp = obj.parentNode.cells[0].innerHTML;
        
        if(pId != tmp)
        {
            pId = tmp;
            oId = obj.parentNode.cells[1].innerHTML;
            
            obj.parentNode.style.background = '#658BC7';
            obj.parentNode.style.color = '#FFF';
        }
        else
        {
            pId = '';
            oId = '';
        }
    }
}

function sameUser()
{
    if(this.value == 'A' || this.value == 'O')
    {
        gId('reciv').value = '$';
        gId('xreciv').value = 'Same User!';
        gId('xreciv').disabled = true;
    }
    else if(this.value == 'R')
    {
        gId('reciv').value = '+';
        gId('xreciv').value = 'Previous User!';
        gId('xreciv').disabled = true;
    }
    else
    {
        gId('reciv').value = '';
        gId('xreciv').value = '';
        gId('xreciv').disabled = false;
    }
}

function prevCon()
{
    if(gId('action').value != '' && pId != '')
    {
        gId('xreciv').extraInfo = gId('action').value+'|:|'+pId;
        return true;
    }
    else
    {
        showFlash("Debe indicar una acción y seleccionar una orden !.");
        return false;
    }
}

function procesar()
{
    if(validate('action,reciv,xreciv,data'))
    {
        if(pId != '' && oId != '' && gId('reciv').value != '@')
        {
            ajaxAction
            (
                new Hash(['*pid => '+pId, '*oid => '+oId, 'action', 'reciv', 'data']),
                $basePath+"home/proc",
                getList
            );
        }
        else
            showFlash('Debe seleccionar una orden!.');
    }
}

function getCicle()
{
    if(oId != '')
    {
        ajaxAction
        (
            new Hash(['*oid => '+oId]),
            $basePath+"home/cicle",
            showCicle
        );
    }
    else
        showFlash('Debe seleccionar una orden!.');
}

function showCicle(response)
{
    var contenedor = new Array();
    var steps = response.responseText.split('|:|');
    var ls = steps.length;
    
    for(var i = 0; i < ls; i++)
        contenedor.push(steps[i].split('=>'));
    
    drawCicle(contenedor);
}

function drawCicle(dek)
{
    showB('visor');
    
    gId('vId').innerHTML = dek[0][0];
    gId('vTy').innerHTML = dek[0][1] == 'A' ? 'OFFSET DIGITAL' : 'GRAN FORMATO';
    gId('vCl').innerHTML = dek[0][2];
    gId('vPr').innerHTML = dek[0][3];
    gId('vFc').innerHTML = dek[0][4];
    gId('vEs').innerHTML = getEstado(dek[0][5]);
    gId('vDu').innerHTML = dek[0][6];
    gId('vVl').innerHTML = '$ '+dek[0][7];
    
    var roles = ['A', 'P', 'I', 'T', 'D', 'C'];
    var nule = '<div class="scr-null"></div>';
    
    var l = dek.length;
    var rd = new Date(dek[0][4]);
    rd = rd.getTime()/1000;
    
    var pos = new Array();
    
    for(var i = 1; i < l; i++)
    {
        var demora = ''
        
        if(dek[i][8] == 'C')
        {
            var date = new Date(dek[i][1]);
            date = date.getTime()/1000;
            demora = toHours(date-rd)
            rd = date;
        }
        else
        {
            var date = new Date(dek[i][1]);
            var now = new Date();
            date = date.getTime()/1000;
            now = now.getTime()/1000;
            demora = toHours(now-date)
            rd = date;
        }
        
        var obj = '<div id="dk_'+i+'" class="scr-obj" onclick="meta(this)">';
        obj += '<b>'+demora+'</b>';
        obj += '</div>';
        
        for(var j = 0; j < 6; j++)
        {
            if(dek[i][5] == roles[j])
            {
                gId('p'+roles[j]).innerHTML += obj;
                pos.push(63+(j*125));
                pos.push((52*i));
            }
            else
                gId('p'+roles[j]).innerHTML += nule;
            
            gId('pc'+roles[j]).innerHTML += nule;
        }
    }
    
    gId('pres').style.height = gId('pA').offsetHeight+'px';
    
    var W = 750;
    var H = gId('pA').offsetHeight;
    
    var c = gId("rower");
    c.width = W;
    c.height = H;
    
    var cpos = pos.length-3;
    var cxt=c.getContext("2d");
    var ap = 2;
    
    for(var j = 0; j <= cpos; j+=2)
    {
        var csty = getColor(dek[ap][9]);
        ap += 1;
        
        cxt.lineWidth = 2;
        cxt.strokeStyle = csty;
        cxt.fillStyle = csty;
        
        cxt.beginPath();
        
        cxt.moveTo(pos[j], pos[j+1]-3);
        cxt.lineTo(pos[j], pos[j+1]+10);
        
        cxt.moveTo(pos[j+2], pos[j+3]-30);
        cxt.lineTo(pos[j+2], pos[j+3]-42);
        
        if(pos[j+2] > pos[j])
        {
            cxt.moveTo(pos[j]-1, pos[j+1]+10);
            cxt.lineTo(pos[j+2]+1, pos[j+3]-42);
        }
        else
        {
            cxt.moveTo(pos[j]+1, pos[j+1]+10);
            cxt.lineTo(pos[j+2]-1, pos[j+3]-42);
        }
        
        cxt.closePath();
        cxt.stroke();
        
        cxt.beginPath();
        
        cxt.moveTo(pos[j+2], pos[j+3]-28);
        cxt.lineTo(pos[j+2]-5, pos[j+3]-37);
        cxt.lineTo(pos[j+2]+5, pos[j+3]-37);
        
        cxt.closePath();
        cxt.fill();
    }
    
    uDek = dek;
}

function closeCicle()
{
    hide('visor');
    gId('pA').innerHTML = '';
    gId('pP').innerHTML = '';
    gId('pI').innerHTML = '';
    gId('pT').innerHTML = '';
    gId('pD').innerHTML = '';
    gId('pC').innerHTML = '';
    gId('pcA').innerHTML = '';
    gId('pcP').innerHTML = '';
    gId('pcI').innerHTML = '';
    gId('pcT').innerHTML = '';
    gId('pcD').innerHTML = '';
    gId('pcC').innerHTML = '';
}

function closeDetaller()
{
    hide('detaller');
}

function closeDet()
{
    hide('oDet');
}

function closeLog()
{
    hide('logger');
}

function meta(elem)
{
    
    var dur = elem.firstChild.innerHTML;
    var ik = parseInt(elem.id.split('_')[1]);
    
    gId('odPid').innerHTML = uDek[ik][0];
    gId('odFec').innerHTML = uDek[ik][1];
    gId('odDur').innerHTML = dur;
    gId('odAct').innerHTML = getAction(uDek[ik][9]);
    gId('odEmiRol').innerHTML = getEstado(uDek[ik][2]);
    gId('odEmiUse').innerHTML = uDek[ik][3];
    gId('odEmiPer').innerHTML = uDek[ik][4];
    gId('odRecRol').innerHTML = getEstado(uDek[ik][5]);
    gId('odRecUse').innerHTML = uDek[ik][6];
    gId('odRecPer').innerHTML = uDek[ik][7];
    gId('odDes').value = uDek[ik][10];
    
    showB('oDet');
}

function getColor(action)
{
    var res = '';
    res = action == 'A' ? '#0000ff': res;
    res = action == 'F' ? '#00ff00': res;
    res = action == 'B' ? '#ffff00': res;
    res = action == 'R' ? '#ff0000': res;
    res = action == 'T' ? '#800080': res;
    res = action == 'O' ? '#000000': res;
    
    return res;
}

function getEstado(ind)
{
    var res = '';
    res = ind == 'A' ? 'Asesoria' : res;
    res = ind == 'P' ? 'Jefatura De Impresión' : res;
    res = ind == 'I' ? 'Impresión' : res;
    res = ind == 'T' ? 'Jefatura De Acabados' : res;
    res = ind == 'D' ? 'Acabados' : res;
    res = ind == 'C' ? 'Entregas' : res;
    
    return res;
}

function getAction(ind)
{
    var res = '';
    res = ind == 'C' ? 'Crear' : res;
    res = ind == 'A' ? 'Anotar' : res;
    res = ind == 'F' ? 'Avanzar' : res;
    res = ind == 'B' ? 'Retroceder' : res;
    res = ind == 'R' ? 'Devolver' : res;
    res = ind == 'T' ? 'Transferir' : res;
    res = ind == 'O' ? 'Aceptar' : res;
    
    return res;
}

function getDeta(elem)
{
    dId = elem.parentNode.parentNode.cells[1].innerHTML;
    ajaxAction
    (
        new Hash(['*param => '+dId]),
        $basePath+"home/deta",
        showDeta
    );
}

function showDeta(response)
{
    matIt = new Array();
    upHash = '';
    var src = '';
    
    var rps = response.responseText.split('|:|');
    var ops = rps[0].split('=>');
    
    dId = ops[0];
    oType = ops[1];
    
    src += '<table class="dtCab">';
    src += '<tr><td colspan="3">'+ops[0]+'</td><td colspan="3">'+(ops[1] == 'A' ? 'OFFSET DIGITAL' : 'GRAN FORMATO')+'</td></tr>';
    src += '<tr><td colspan="3">'+ops[2]+'</td><td colspan="3">'+ops[3]+'</td></tr>';
    src += '<tr><td colspan="2">'+ops[4]+'</td><td colspan="2">'+(getEstado(ops[5]))+'</td><td colspan="2">'+ops[6]+'</td></tr>';
    src += '<tr><td colspan="2">$ '+ops[7]+'</td><td colspan="2">'+ops[8]+' %</td><td colspan="2">$ '+ops[9]+'</td></tr>';
    src += '<tr><td colspan="6"><textarea rows="5" readonly="readonly">Observaciones Generales: '+ops[10]+'</textarea></td></tr>';
    src += '</table><br />';
    
    var lit = rps.length;
    for(var i = 1; i < lit; i++)
    {   
        var ips = rps[i].split('=>');
        
        var ait = [ips[5], ips[2], ips[4], ips[15]];
        matIt.push(ait);
        
        src += '<table class="dtItm">';
        src += '<tr><td colspan="2">'+ips[5]+'</td><td colspan="2">'+ips[1]+'</td><td colspan="2">'+ips[3]+'</td></tr>';
        src += '<tr><td colspan="3">'+ips[11]+'</td><td colspan="3">'+(ips[12] != '@' ? ips[12] : 'Al entregar!')+'</td></tr>';
        src += '<tr><td colspan="3">'+ips[6]+'</td><td colspan="3">'+ips[7]+'</td></tr>';
        src += '<tr><td colspan="6">'+(ips[14] != '@' ? ips[14] : 'Sin Acabados!')+'</tr>';
        src += '<tr><td colspan="6"><textarea rows="5" readonly="readonly">Observaciones: '+(ips[13] != '@' ? ips[13] : 'Sin observaciones!')+'</textarea></td></tr>';
        src += '<tr><td colspan="6">';
        src += '<a href="'+$storage+'/scribo/files/'+ips[10]+'" target="_blank"><img src="'+$imgPath+'/adown.png" title="Descargar Archivo!" /></a>';
        
        if($jar == 'A')
            src += '<img style="cursor: pointer;" src="'+$imgPath+'/refreshb.png" onclick="falseFile(\''+ips[0]+'|:|'+ips[10]+'|:|'+ips[5]+'|:|'+ips[11]+'\');" title="Actualizar Archivo!" />';
        
        src += '</td></tr>';
        src += '</table><br />';
    }
    
    gId('dSpace').innerHTML = src;
    showB('detaller');
}

function getDesc(elem)
{
    show('logger');
    gId('lgOid').innerHTML = elem.parentNode.parentNode.cells[1].innerHTML;
    gId('lgDes').value = elem.parentNode.parentNode.cells[6].innerHTML;
}

function refresh(response)
{
    prClear();
    
    if(response.responseText != '')
    {
        var src = '';
        var rows = response.responseText.split('|:|');
        var lr = rows.length;
        
        for(var i = 0; i < lr; i++)
        {
            src += '<tr>';
            
            var cells = rows[i].split('=>');
            var lc = cells.length;
            
            var otip = cells[2] == 'A' ? 'OFFSET DIGITAL' : 'GRAN FORMATO';
            
            var tmod = parseInt(cells[5])-parseInt(cells[6]);
            tmod = tmod > 0 ? toHours(tmod)+' Restantes.' : '<b style="color: red;">'+toHours(-1*tmod)+' De Restraso.</b>'; 
            
            src += '<td class="scr-hidden">'+cells[0]+'</td>';
            src += '<td>'+cells[1]+'</td>';
            src += '<td>'+otip+'</td>';
            src += '<td>'+cells[3]+'</td>';
            src += '<td>'+cells[4]+'</td>';
            src += '<td>'+tmod+'</td>';
            src += '<td class="scr-hidden">'+cells[7]+'</td>';
            src += '<td>';
            src += '<img src="'+$imgPath+'/log.png" onclick="getDesc(this);" title="Ver Descripción!." />';
            src += '<img src="'+$imgPath+'/det.png" onclick="getDeta(this);" title="Ver Detalles!." />';
            src += '</td>';
            src += '</tr>';
        }
        
        idTim = setTimeout(function(){ gId('lOrder').innerHTML = cabs+src; clearTimeout(idTim); }, 250);
    }
    else
    {
        idTim = setTimeout(function(){ gId('lOrder').innerHTML = cabs+'<tr><td colspan="6">NO DATA</td></tr>'; clearTimeout(idTim); }, 250);
    }
}

function prClear()
{
    if(gId('xreciv') != null)
    {
        gId('xreciv').value = '';
        gId('reciv').value = '';
    }
    
    if(gId('action') != null)
        gId('action').value = '';
    
    if(gId('data') != null)
        gId('data').value = '';
    
    firmaCode = '';
    
    if(gId('showFirma') != null)
        hide('showFirma');
}

function verifyEnt()
{
    if(oId != '')
    {
        ajaxAction
        (
            new Hash(['*oid => '+oId]),
            $basePath+"home/veri",
            confEnt
        );
    }
    else
        showFlash('Debe seleccionar una orden!.');
}

function confEnt(response)
{
    if(response.responseText == 'Y')
    {
        showFlash('Registre la firma del cliente para completar la entrega!...');
        show('showFirma');
    }
    else
        showFlash('La orden debe registrar un procesos de "Aceptación" antes de ser entregada!.');
}

function firmaApply()
{
    ajaxAction
    (
        new Hash(['*pid => '+pId, '*oid => '+oId, '*firma => '+firmaCode, '*data => '+gId('obsEntre').value]),
        $basePath+"home/entrega",
        pic
    );
}

function pic(response)
{
    if(parseInt(response.responseText) > 0)
        document.location = $basePath+"home/"+response.responseText+"/visor";
    else
        showFlash("Imposible procesar la entrega!.");
}

/* ####### Archivos ####### */

function falseFile(fid)
{
    fid = fid.split('|:|');
    idActu = fid[0];
    oldFile = fid[1];
    oldName = fid[2];
    oldSig = fid[3];
    falseClick('fileIn');
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
    tmpFile = this.result;
    uploader();
}

function uploader()
{
    if(confirm("Seguro que desea actualizar el archivo, la version anterior se eliminara?."))
    {
        upUrl = $storage+'/scribo/repository.php';
        upTime = new Date().getTime();
        upName = magnaFile.name;
        upOut = '@';
        upData = tmpFile;
        upLimit = upData.length;
        upAction = okUp;
        
        gId('upLabel').innerHTML = upName;
        showB('uploader');
        
        partialUpload('');
    }
}

function okUp()
{
    var porcen = parseInt((1-(upData.length / upLimit))*100);
    gId('upBar').style.width = porcen+'%';
    
    if(upHash != '')
    {
        hide('uploader');
        localPurge(oldFile);
    }
}

function localPurge(fileTar)
{
    ajaxAction
    (
        new Hash(['*action => delete', '*target => '+fileTar]),
        $storage+'/scribo/repository.php',
        updateDB
    );
}

function updateDB()
{
    ajaxAction
    (
        new Hash(['*param => '+dId, '*otype => '+oType, '*iid => '+idActu, '*oname => '+oldName, '*nname => '+magnaFile.name, '*ostorage => '+oldFile, '*nstorage => '+upOut, '*osignature => '+oldSig, '*nsignature => '+upHash]),
        $basePath+"home/updfil",
        showDeta
    );
}

function cancelOrder()
{
    showB('flotter');
}

function actFlot()
{
    hide('flotter');
    
    if(this.id == 'flOk')
    {
        var oval = gId('flVl').value != '' ? gId('flVl').value : '@';
        
        ajaxAction
        (
            new Hash(['*oid => '+dId, '*oval => '+oval]),
            $basePath+"home/ordcan",
            okCancel
        );
    }
    
    gId('flVl').value = '';
}

function okCancel()
{
    hide('detaller');
    getList();
}

/*--- Perdida ---*/

function viewPerdida()
{
    var lit = matIt.length;
    gId('perItem').innerHTML = '<option value="">Seleccione...</option>';
    for(var i = 0; i < lit; i++)
    {
        gId('perItem').innerHTML += '<option value="'+i+'">'+matIt[i][0]+'</option>';
    }
    
    if(oType == 'B')
    {
        hide('perPag1');
        hide('perPag2');
    }
    else
    {
        gId('perPag1').style.display = 'table-cell';
        gId('perPag2').style.display = 'table-cell';
    }
    
    showB('calper');
}

function addPerdida()
{
    if(oType == 'A')
    {
        if(validate('perItem,perPaginas,perCantidad'))
        {
            var iid = parseInt(gId('perItem').value);
            if($jar == 'I')
            {
                var pags = parseInt(gId('perPaginas').value);
                var cant = parseInt(gId('perCantidad').value);
                
                var vmat = parseInt(matIt[iid][1]);
                var vtin = parseInt(matIt[iid][2]);
                
                var tval = ((vmat+vtin)*pags)*cant;
                
                vvPer += tval;
                
                gId('perView').value += matIt[iid][0]+':    $ '+tval+"\n";
                gId('perValor').value = vvPer;
            }
            else if($jar == 'D')
            {
                var pags = parseInt(gId('perPaginas').value);
                var cant = parseInt(gId('perCantidad').value);
                
                var vmat = parseInt(matIt[iid][1]);
                var vtin = parseInt(matIt[iid][2]);
                var vaca = parseInt(matIt[iid][3]);
                
                var tval = ((vmat+vtin+vaca)*pags)*cant;
                
                vvPer += tval;
                
                gId('perView').value += matIt[iid][0]+':    $ '+tval+"\n";
                gId('perValor').value = vvPer;
            }
        }
    }
    else
    {
        if(validate('perItem,perCantidad'))
        {
            var iid = parseInt(gId('perItem').value);
            if($jar == 'I')
            {
                var cant = parseInt(gId('perCantidad').value);
                
                var vmat = parseInt(matIt[iid][1]);
                var vtin = parseInt(matIt[iid][2]);
                
                var tval = (vmat+vtin)*cant;
                
                vvPer += tval;
                
                gId('perView').value += matIt[iid][0]+':    $ '+tval+"\n";
                gId('perValor').value = vvPer;
            }
            else if($jar == 'D')
            {
                var cant = parseInt(gId('perCantidad').value);
                
                var vmat = parseInt(matIt[iid][1]);
                var vtin = parseInt(matIt[iid][2]);
                var vaca = parseInt(matIt[iid][3]);
                
                var tval = (vmat+vtin+vaca)*cant;
                
                vvPer += tval;
                
                gId('perView').value += matIt[iid][0]+':    $ '+tval+"\n";
                gId('perValor').value = vvPer;
            }
        }
    }
    
    clear('perItem,perPaginas,perCantidad');
    gId('perItem').focus();
}

function applyPerdida()
{
    if(validate('perObs,perValor'))
    {
        var lVal = parseInt(gId('perValor').value);
        
        if(lVal < vvPer)
        {
            gId('perValor').value = vvPer;
            gId('perValor').focus();
            alert('El valor de la pérdida no puede ser menor al calculado!.');
        }
        else
        {
            ajaxAction
            (
                new Hash(['*param => '+dId,'*obs => '+gId('perObs').value,'*value => '+gId('perValor').value]),
                $basePath+"home/appper",
                okPerdida
            );
        }
    }
}

function okPerdida(response)
{
    alert('Pérdida aplicada!');
    closePerdida();
}

function clearPerdida()
{
    vvPer = 0;
    clear('perView,perValor,perObs,perItem,perPaginas,perCantidad');
    gId('perItem').focus();
    
    gId('perItem').style.borderColor= "";
    gId('perPaginas').style.borderColor= "";
    gId('perCantidad').style.borderColor= "";
    gId('perObs').style.borderColor= "";
    gId('perValor').style.borderColor= "";
    gId('perItem').style.boxShadow= "";
    gId('perPaginas').style.boxShadow= "";
    gId('perCantidad').style.boxShadow= "";
    gId('perObs').style.boxShadow= "";
    gId('perValor').style.boxShadow= "";
}

function closePerdida()
{
    hide('calper');
    
    clearPerdida();
}
