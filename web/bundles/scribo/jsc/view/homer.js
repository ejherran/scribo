var ctlUrl = 'home'                                          // Url del controlador base.

var pId = '';
var oId = '';

function $_init()
{
    if($mode != '0')
    {
        gId('lOrder').onclick = fixPid;
        gId('proce').onclick = procesar;
        gId('xreciv').onkeydown = FindHelp;
        gId('xreciv').beforeAction = prevCon;
        gId('action').onchange = sameUser;
        gId('cicle').onclick = getCicle;
    }
}

function fixPid(event)
{
    var obj = event.target;
    if(obj.nodeName == 'TD')
    {
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
    if(this.value == 'A')
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
                refresh
            );
        }
        else
            showFlash('Debe seleccionar una orden!.');
    }
}

function getCicle()
{
    if(pId != '' && oId != '' && gId('reciv').value != '@')
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
        
        var obj = '<div class="scr-obj">';
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
}

function getColor(action)
{
    var res = '';
    res = action == 'A' ? '#0000ff': res;
    res = action == 'F' ? '#00ff00': res;
    res = action == 'B' ? '#ffff00': res;
    res = action == 'R' ? '#ff0000': res;
    res = action == 'T' ? '#800080': res;
    
    return res;
}

function refresh(response)
{
    document.location.reload();
}
