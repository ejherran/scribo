window.onload=$init;

var H = 0;
var R = 0;
var P = 0;
var B = -1;

var idT = null;

function $init()
{
    getList();
}

function getList()
{
    B += 1;
    
    if((B % 10) == 0)
    {
        showB('bs');
        idT = setTimeout(ajaxList, 5000);
    }
    else
        ajaxList();
    
}

function ajaxList()
{
    ajaxAction
    (
        new Hash(['*param => *']),
        $basePath+"board/listp",
        refresh
    );
}

function refresh(response)
{
    gId('z3').innerHTML = '';
    gId('p').innerHTML = '';
    
    if(response.responseText != '')
    {
        var rows = response.responseText.split('|:|');
        var lr = rows.length;
        
        for(var i = 0; i < lr; i++)
        {
            var cells = rows[i].split('=>');
            
            var tck = parseInt(cells[5])-parseInt(cells[6]);
            tck = tck >= 0 ? toHours(tck) : '<b style="color: red;">'+toHours(-1*tck)+'</b>'; 
            
            var src = '<div class="lab">';
            src += '<table>';
            src += '<tr><td>Nº '+cells[0]+'</td></tr>';
            src += '<tr><td>'+tck+'</td></tr>';
            src += '<tr><td>'+cells[3]+'</td></tr>';
            src += '</table>';
            src += '</div>';
            
            gId('z3').innerHTML += src;
            gId('p').innerHTML += '<div class="bar" style="width: '+getWidth(cells[1], cells[2])+'%;"></div>';
        }
    }
    
    H = gId('z3').offsetHeight+50;
    R = gId('r').offsetHeight;
    
    gId('c1').style.height = H+'px';
    gId('c2').style.height = H+'px';
    gId('c3').style.height = H+'px';
    gId('c4').style.height = H+'px';
    gId('c5').style.height = H+'px';
    gId('c6').style.height = H+'px';
    
    if(B % 10 == 0)
    {
        clearTimeout(idT);
        hide('bs');
    }
    
    idT = setInterval(espera1, 7500);
}

function getWidth(est, act)
{
    var res = 1;
    
    res = est == 'A' ? 1 : res;
    res = est == 'P' ? 18 : res;
    res = est == 'I' ? 35 : res;
    res = est == 'T' ? 52 : res;
    res = est == 'D' ? 69 : res;
    res = est == 'C' ? 86 : res;
    res = (est == 'C' && act == 'O')  ? 98: res;
    
    return res;
}

function toHours(seconds)
{
    var hours = parseInt(seconds/3600);
    seconds = seconds%3600;
    var minuts = parseInt(seconds/60);
    seconds = parseInt(seconds%60);
    
    hours = hours < 10 ? '0'+hours : ''+hours;
    minuts = minuts < 10 ? '0'+minuts : ''+minuts;
    seconds = seconds < 10 ? '0'+seconds : ''+seconds;
    
    return hours+':'+minuts+':'+seconds;
}

function espera1()
{
    clearInterval(idT);
    idT = setInterval(baja, 40);
}

function baja()
{
    if((P+R) < H)
    {
        gId('r').scrollTop = P;
        P = P+1;
    }
    else
    {
        clearInterval(idT);
        idT = setInterval(espera2, 15000);
    }
}

function espera2()
{
    clearInterval(idT);
    idT = setInterval(sube, 40);
}

function sube()
{
    if(P > 0)
    {
        gId('r').scrollTop = P;
        P = P-1;
    }
    else
    {
        clearInterval(idT);
        idT = setInterval(actualiza, 7500);
    }
}

function actualiza()
{
    clearInterval(idT);
    getList();
}
