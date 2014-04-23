import random as rn

def deca(v):
    if(v < 10):
        return '0'+str(v)
    else:
        return str(v)

def fecha(Y, M, D):
    return deca(Y)+'-'+deca(M)+'-'+deca(D)

def hora():
    hh = rn.randint(0, 23)
    mm = rn.randint(0, 59)
    ss = rn.randint(0, 59)
    
    return deca(hh)+':'+deca(mm)+':'+deca(ss)

meses = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31] 
y = 2005
m = 0
d = 0
l = 0

c = 0
bnk = [];
while(l < 10):
    
    if(d < meses[m]):
        fc = fecha(y, m+1, d+1)
        cfd = rn.randint(5, 50)
        for i in range(cfd):
            hr = hora();
            bnk.append("("+str(c+1)+", 1, 72, '"+fc+" "+hr+"', "+str(rn.randint(10000, 3000000))+", 'none')")
            c = c+1
            
            if (c % 500) == 0:
                print "INSERT INTO `perdida` (`id`, `usuario_id`, `orden_id`, `date`, `valor`, `data`) VALUES \n"+",\n".join(bnk)+";"
                bnk = [];
        
        d += 1
    else:
        m += 1
        if m == 12:
            m = 0
            y += 1
            l += 1
            
        d = 0
print "INSERT INTO `perdida` (`id`, `usuario_id`, `orden_id`, `date`, `valor`, `data`) VALUES \n"+",\n".join(bnk)+";"
