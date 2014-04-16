var canvas;
var ctx;
var rect;

var ox;
var oy;

var code;
var pick;

function iniCanvas()
{
    canvas = gId('firma');
    
    ox = -1;
    oy = -1;

    code = '';
    pick = false;

    gId('getFirma').onclick = getFirma;
    gId('clearFirma').onclick = clearFirma;
    gId('showFirma').onclick = showFirma;

    canvas.onmousedown = activeFirma;
    canvas.onmouseup = anuleFirma;
    canvas.onmouseover = anuleFirma;
    canvas.onmousemove = moveFirma;
}

function clearFirma()
{
    anuleFirma();

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    gId('firmaCode').value = '';
}

function activeFirma()
{
    pick = true;
    ox = -1;
    oy = -1;
}

function anuleFirma()
{
    pick = false;
    ox = -1;
    oy = -1;
}

function moveFirma(evt)
{
    if(pick)
    {
        
        var x = evt.clientX - rect.left;
        var y = evt.clientY - rect.top;
        
        ctx.lineWidth = 3;
        ctx.strokeStyle = 'blue';
        
        ctx.beginPath();
        
        if(ox == -1 && oy == -1)
        {
            ox = x;
            oy = y;
        }
        else
        {
            ctx.moveTo(ox,oy);
            ctx.lineTo(x,y);
            ctx.stroke();
            
            ox = x;
            oy = y;
        }
        
        ctx.closePath();
        
    }
}

function getFirma()
{
    code = canvas.toDataURL();
    firmaCode = code;
    hide('ctFirma');
    
    if(typeof firmaApply != 'undefined')
        firmaApply();
}

function showFirma()
{
    window.scroll(0,0);
    show('ctFirma');
    ctx = canvas.getContext("2d");
    rect = canvas.getBoundingClientRect();
}
