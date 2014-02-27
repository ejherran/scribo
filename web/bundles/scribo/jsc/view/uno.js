/* ####### Variables ####### */

var ctlUrl = 'uno'                                          // Url del controlador base.
var magnaFile = '';

var findHelpIndex = -1;
var findHelpBlur = true;

/* ####### Init Global ####### */

function $_init()
{
    iniCanvas();
    
    gId('xxFileIn').onclick = falseFile;
    gId('fileIn').onchange = inFile;
    
    gId('xOrdClient').onkeydown = FindHelp;
    gId('xOrdMaterial').onkeydown = FindHelp;
    gId('xOrdTinta').onkeydown = FindHelp;
}

/* ####### Archivos ####### */

function falseFile(event)
{
    falseClick('fileIn');
}

function inFile(event)
{
    magnaFile = event.target.files[0];
    gId('xFileIn').value = magnaFile.name;
}

function loadFile()
{
    var reader = new FileReader();
    reader.onload = createImg;
    reader.readAsDataURL(magnaFile);
}

function createImg(event)
{
    gId('imgShower').innerHTML += '<tr><td><img class="scr-thumb" src="'+this.result+'" />';
}

