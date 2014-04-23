var ctlUrl = 'repo'                                          // Url del controlador base.

function $_init()
{
    if(gId('year') != null)
    {
        var opts = '';
        var fecha = new Date();
        var ay = parseInt(fecha.getFullYear());
        
        for(var i = ay; i >= 1975; i--)
            opts += '<option value="'+i+'">'+i+'</option>';
        
        gId('year').innerHTML += opts;
    }
    
    gId('gen').onclick = gener;
    gId('xorden').onkeydown = FindHelp;
}

function gener()
{
    if(validate('repo'))
    {
        var rp = gId('repo').value;
        if(rp == 'conday' || rp == 'conweek' || rp == 'enday' || rp == 'enweek' || rp == 'perday' || rp == 'perweek')
        {
            if(validate('year,month,day'))
            {
                gId('visor').src = $basePath+'repo/'+gId('year').value+'-'+gId('month').value+'-'+gId('day').value+'/'+rp;
            }
        }
        else if(rp == 'conmonth' || rp == 'enmonth' || rp == 'permonth')
        {
            if(validate('year,month'))
            {
                gId('visor').src = $basePath+'repo/'+gId('year').value+'-'+gId('month').value+'/'+rp;
            }
        }
        else if(rp == 'conyear' || rp == 'enyear' || rp == 'peryear' || rp == 'condeca' || rp == 'endeca' || rp == 'perdeca')
        {
            if(validate('year'))
            {
                gId('visor').src = $basePath+'repo/'+gId('year').value+'/'+rp;
            }
        }
        else if(rp == 'logcat')
        {
            if(validate('orden,xorden'))
            {
                gId('visor').src = $basePath+'repo/'+gId('orden').value+'/'+rp;
            }
        }
    }
}
