// Funsion de inicio maestro 
window.onload=$init;

function $init()
{
    gId("flash-zone").onclick = hideFlash;
    gId("qck-chpass").onclick = switchChpass;
    gId("wgCambiar").onclick = saveChpass;
    gId("wgCancelar").onclick = cancelChpass;
}

function showFlash(msg)
{
    gId("flash-zone").innerHTML += "<li>"+msg+"</li>";
}

function hideFlash()
{
    this.innerHTML = '';
}

function switchChpass()
{
    if(gId("chpass").style.display != "block")
        gId("chpass").style.display = "block";
    else
        gId("chpass").style.display = "none";
}

function saveChpass()
{
    if(gId("wgOldpass").value != '' && gId("wgPass").value != '' && gId("wgRepass").value != '')
    {
        if(gId("wgPass").value == gId("wgRepass").value)
        {
            ajaxAction
			(
				new Hash(["wgOldpass", "wgPass"]),
				$basePath+"chpass",
				resulChpass
			);
            
            cancelChpass();
        }
        else
            showFlash("Las nuevas contraseñas no coinciden!");
    }
    else
        showFlash("Datos incompletos!");
}

function resulChpass(response)
{
    showFlash(response.responseText);
}

function cancelChpass()
{
    clearChpass();
    switchChpass();
}

function clearChpass()
{
    gId("wgOldpass").value = '';
    gId("wgPass").value = '';
    gId("wgRepass").value = '';
}

