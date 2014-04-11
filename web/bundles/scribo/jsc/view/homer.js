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
        gId('xreciv').extraInfo = 'action';
        gId('action').onchange = sameUser;
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
    else
    {
        gId('reciv').value = '';
        gId('xreciv').value = '';
        gId('xreciv').disabled = false;
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

function refresh(response)
{
    document.location.reload();
}
