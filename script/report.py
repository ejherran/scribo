# -*- coding: utf-8 -*-

import dbconf as db
import commands as cm
import base64 as b64

HF = cm.getoutput('date +%H');

cfs = cm.getoutput("mysql -h "+db.Host+" -P "+db.Port+" -u "+db.User+" -p"+db.Pass+" "+db.Name+" -e \"select conexion.host, conexion.port, conexion.path, conexion.user, conexion.pass from conexion, licencia, servicio where servicio.code='SCRB' and licencia.servicio_id=servicio.id and licencia.fin>=curdate();\"")
cfs = cfs.split("\n")
cfs = cfs[1:]

for c in cfs:
    ps = c.split("\t")
    us = b64.b64decode(ps[3]);
    pw = b64.b64decode(ps[4]);
    tar = cm.getoutput("mysql -h "+ps[0]+" -P "+ps[1]+" -u "+us+" -p"+pw+" "+ps[2]+" -e \"select report from configuracion limit 1;\"")
    tar = tar.split("\n");
    if(len(tar) > 1):
        tar = tar[1]
        tar = tar.split(';')
        for t in tar:
            tm = t.split(',')
            mail = tm[0]
            hora = tm[1]
            
            if int(hora) == int(HF):
                ien = cm.getoutput("mysql -h "+ps[0]+" -P "+ps[1]+" -u "+us+" -p"+pw+" "+ps[2]+" -e \"select count(id) as cantidad, sum(valor) as valor, DATE_SUB(CURDATE(), INTERVAL 1 DAY) as fecha from entrega where date like concat(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '%');\"");
                ien = ien.split("\n");
                ien = ien[1]
                ien = ien.split("\t");
                
                iper = cm.getoutput("mysql -h "+ps[0]+" -P "+ps[1]+" -u "+us+" -p"+pw+" "+ps[2]+" -e \"select count(id) as cantidad, sum(valor) as valor, DATE_SUB(CURDATE(), INTERVAL 1 DAY) as fecha from perdida where date like concat(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '%');\"");
                iper = iper.split("\n");
                iper = iper[1]
                iper = iper.split("\t");
                
                memo = "Reporte simplifcado de entregas y perdidas.\n\n"
                memo += "Cantidad de entregas: "+ien[0]+".\n"
                memo += "Valor de entregas: "+ien[1]+".\n"
                memo += "Cantidad de pérdidas: "+iper[0]+".\n"
                memo += "Valor de pérdidas: "+iper[1]+".\n\n"
                memo += "Para un reporte detallado identifiquese en la plataforma y pulse sobre el sigiente link o copielo en la barra de navegacion:\nhttp://181.55.250.253:8777/scribo/app.php/repo/"+ien[2]+"/conday"
                cmd = 'echo "'+memo+'" | mail -s "SCRIBO REPORT '+iper[2]+'" '+mail
                cm.getoutput(cmd)
