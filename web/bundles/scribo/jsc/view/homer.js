var pId = '';

function $_init()
{
    gId('lOrder').onclick = fixPid;
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
            obj.parentNode.style.background = '#658BC7';
            obj.parentNode.style.color = '#FFF';
        }
        else
            pId = '';
    }
}
