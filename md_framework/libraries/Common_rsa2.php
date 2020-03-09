<?php
/**
 * rsa2类
 */
class CI_Common_rsa2
{
    private static $PRIVATE_KEY = "-----BEGIN RSA PRIVATE KEY-----
MIISKQIBAAKCBAEAsnqFPJQM0x3RhAbwpKEPTsfC8mE1gze5iczGbuosVrIlp5q9
Ifawp/WHk/ji4q/Uz07Ivkuvm+hbhLB1Jdqq4stuLmg9cxK9HWKs6nU4QqpceEwE
6Imiz38bCPLZU5geYjnBGPBgu7wY9PD/dSwrxhIB/FuKGtocM/100VfVLYFs7/wA
K/WhkGLBPo6YoS3h8+DT9Fqv0jDhcvYYk/IWLEegO3qCjIl0KCSGSdHvJp5Xrg/B
DSpA2oYL+2j9OPPfyfRqUKP4seDPEpFfCnhS4tx/LfBrJruKf4ztVfcDojjZWztS
+Tk7a83/i6USPxbYfqMH0pdKplFVL0x/S2KRyqU85E4chohtixMtrE9TPqk/xuqb
/NFV8bxGPY2YFH4faB9EJrS9QF/vAxRJbR2NOIbubg3JotkFSOAnAwcg8pjz0PKl
P0HeQ2vTtPxzGm2FropVWTPsdm5G1Dlj1nVM8Ox0CZKZeRsdjsyXLAwMZqKNEUms
neAYDefM9202I+pLnI4eeiAyUEbzkbAxWtQ1HxjWLaoX7RNMDVdFlmx7OOFME14n
jfr+Rn+gLs7b3YKSCW5I2hfuJBaL2tXgy+hJFjtCO006+Xj54ct+NXJDHSqdCAxh
KvIy5UEqOPIfKZeiaEvuYNvDjVL8DTG7lgIenwUgvD0+fZESitWlifsuzMEdXMTp
ek1GJC5UMX+arZX95A+X5KAE3U13zRBYK6PhavG0Iqac0yKIILGX+2zI6PN9st53
3fzSA2I1paS5IyYsMfqdRO6ET0m7W1iTN6S9ekdFX5cZDIgmfuRBLuUgoSqBygPW
/QivryyHrNgO3CPthHg7u5Gdm4gGHOAc3Pwia8MlDzxKEU1bw1X4Mk0vzg9S1q+p
LToa2BOi2TrSecXMGVCizsxysfggKUhrbmE36pMXhgq5QORZBUfzP2MGlYgvh+c1
+2p0Rh1QZOY/i+PS5uulP7jWvAz/5osyZLZnI5eNw9znGdesP9CXeU21V6V/QgRw
auywC3WD0Bzo5WsxHQAxkFSt2DY29qsPvgpM8cRwWWw6+Qudy/znGb7qyF2gO9Xj
iuUNizf22DPi2T7em7OL30BM8qY8n+sqpXQO/7WNIDPm+dTd2rEQ7eEjIYKVV2ue
EjCZyWdpt82kfftwOqsbWhupFBt8myvtWBub6xm76muT5t4aXGWmekxpIQNAXIE8
Z247DKr5pXYF7ZkGBtDsNRlzLPZSnilKZafi+x9UW7fkIPqSXMUZK0vbumA5yBNO
3rK92G6fiLiZttu65QgcjTXUGHc/fw0iSdaPfrFZXe+TEMzLBbgPXPKI7wHxjrBP
yV1Z/XNeGqWuTdF/ZkOkEDZMdfk+j0Iub+xmfQIDAQABAoIEAQCrWKDtZZ1iWXA5
9oe1JcMSeQmZtOWxEiCYQPYIqdzjZqhr3kzRfIpg1YHR99Gqm/ANDq/+sZ7oaQzj
uJzfOK1yiqc++mh0P3s76O3lMaBPsEqSWzkjHnAmEPsdfjhS3uncl2Hg1XWpBo5X
Kb+2+C8WO1eYtTFDUj5dU2mBSXep9mVliGOJ0InJmmW37yJtKyWQAzq5jw7ZFoB6
4/T4pCPRYvpb3+PRlGzl1RrvaBLW2pGG1C0cvIICyUpblZH1yXwB4RroTGOIMjHa
u6Go5Zd9oP6gVf4o80el6OA59amRZk6WHcAs8FsxxlUd9d4sn3w9J7ts3D6YqeDE
Rga3kuwwxq5M6QjNx9CTAliI4qcis049ZCNZdoeySnSMx5FHq0XvqoS9PCusz+0g
4asUjJh1umTRV39LTlqaekg5yfBBt8HryegHcO83fx+C1BKVPqxmwvvpZcYQdRKe
0HIfBAJYEkncntekwvl/jL9dVDvblRclj7ue3fN59h57NGgT1u28YVy1Fo/3LgbM
yYC4mJ5fOnL06CWMP0h2TmDKZ19tXi6xMWV8E4lmmURpa8L9SEt3rl9Wsj64esTN
1SO530m7hCyzzhNeSlxr//cszIPOTojvnyLyHg/qtMMaZKiyP5d11eAHMTTuSssc
x7A19db8B+pDjfCekFZi33XLoFBv1SIQmn/v+ahz9Tg+fkc+W3DfyzMKOV2zrxNR
ez2IUx22K8I9z+AlVT0rwnEdREdh9kwf3uCepCLWEabL3eRetWTN4nj1eVwX9smq
3pey069dJy9aTgFZ8srGbhNu5SrmWcUYLq9eAZhwdK1EQfkrKxfAv4dHoHLuxW1D
Dx/zwgxD01ICuEFaDi0ubtvDHKkLua5dYIIm3n3iE6coS/ng/wkU7IeojEBfUkQa
C6VHcbRhklAjSIPOyC0YjFBnwDOIXc5YItL3ecIs9oUYkslAcK2nP1jlarT8UpaC
zWmuKQES3VuN0/lWDoR+EdlS9FDniOWS2hOsaf0FZaq2BdCFxEmgBCYNonqQwAtm
rW/Y2dD90jd4D6srOQvI/w3C88zsaUPgkZPKYY9JBL8pyVO6P3E8Dpkq9fghB01I
fqPe17erFrlF2ZWMf9zGpaEbEWKU22wpB5luzMI8dKBWWBr0NkqIz5Tk8IUHaklY
e6M442/1ylF0hbbprtZKfGlcmwdY1f2PNEcrqZJe3oPSTmiYWs6Y24npXqRSHpzt
XSC7pmVBmVfJAsfDy3vipvtRKK1czHkRmEQ2niNokvKVlWGJ09dRswCLi220uTka
XAt/qcr6R+x6Ntr5ISD7SuSrk8UShJtrrUINquV/o4dhCATo7M7Ydz3ItH05iJJM
vHTSnxQpAoICAQDstFwMNkCzlJfuDb0N4Rqqy5KSST6ckah8l3zfcB7Rn4yP9wcy
Kn7vKDO+/+XgKjcV/d8WLCZI1tK5Gtsy7Ex2LLzZQCdFYra4VW0bW87khonXbZB/
XO8R5/6wS/jOxCjAn4TEnqP2kuAO2wbdcye/x1p2sMP1KR2PI/ah9WPngAlt5EtI
q+13QPmzutKfwCr2+yxlf+Y1sM4/kGy7KqQ7002TsOU4h2HE1hmwyioEEXsGGGZV
zKgtzBfe6MjHDs+sLUYJohdKSeOzT2EKBp51fZnGbFfONUAFOzgGWuaKx9PsPdcE
9HpDCcfdDWpq/I792AXH+H4hHx2SYbeg581RHdcJbLF4KA8MiwNqik1UHPafZauS
tNWdWujOuDs+GxVvieW6jNz8djZ7S6di/LLswh62JSukkTih1V69iKG43H5VDdnl
oVKpob0mpPdSNta404iBwF2AiPBSO052tOuyhHsxV41Az2U3lR6k0cg0c4Eivvgu
fnPJdrRqypM3TqSZFwptl6Q9/6RooEKTG5/2rHVWoe+PCMG6d8DWKP9Ys1IAzKEd
J9JsbfXo9t+4RxqCtxWKwIvMWSKrDiUpB1H8ooLkmoDcKy4bWVgMJQ2KnpnRRPvl
0/mt3f5kRk9Ko41imMC1A6lH8GKlu2t+c6ta4VnrCKdm4qH3Yg3ZOR2wlwKCAgEA
wQcUc3eeWv2unZcduXaceu6lB+2y1RDx6OwAgLvHDJVEHglShly+DP3+gdrM1SxX
nvG3Ukajf8QlMKDvVJrrTnLGpw6n8TqzFV/vxUpF3ETSDmE6dmbOiIEVpsXFmh5p
+YkBz9daxvPNNaPeC2k+kcQ3FeiigI00DHMQSLmLSpP4tYpBekdGjiivJdpwJ3PJ
rOMcFPIMA0rsdJOy3KyFuHT946p42oC0ZywGilMBuzH0GeaxrkxWKSdLscq/aECk
hCTDIHmYdPZB46gkx41zEsHxK8eW3JqX0CDjhcPViz5el9XBwk2x/7R7hPI3XlN2
vGmY8b16XC4G+UkGlxTcyf/m9yj3Kg3zzPtgmBEfTp57EW7k1PqSua1btgF35E3z
FACt9aDLNx1e/7ekuAdetcWxnuGkFhIvzn+JY7D+BmkTP916b5n2JrAHPjBDO7Js
pQHGw/wP2X0tF/I6vI7Q17sszob7P1xvkTiEYtTTiPTZOM0XP56SZt0BtM1NSmGq
wSCw6JuoHJL1Wkqx5xx3eSzkfvDIwRLR3XELr29E/VAylbMgxFvYIfgjvOL5M4QX
5q3Wbgjvn76ISELZlvOpBPYCdHDKZdMBdT+Bx5fFpcFDW4cD6htSZKyzr/X+/erO
fsQImJ7E9ZmSmZO91Cdy8BmR4n+zQ45rJH0pQmlysAsCggIBANxzmerxuE7ITLu0
ZegAR6LcWBUQTFTrv7zSJcW/maRn8TS/wB7zvyakDZWMpR6iko1T1SrEUZ+zG6y0
G4SQ2SGSxwubGTghGMYTIbvCRK1HtCdEbrh/6FSH+gddqkuAG+hSaQfqLBDgd/a9
/OXQcyvE0jcKqGazNUl0GCZ88d/QundzkL4NCevwQt8mVzXbduhxw8aAWPqWQXCn
5OxoaW8ie/TaYpHEXMVDFcg2cO07DpX9sYmlQtjsZSsBKMVjwPy9aaZdJU3WBVCh
1GHPClOod8h6bUirBYYZwjMBBAgq1fAHJDQEMb4v5Mm2ze74a4B1aeT1RRs57IPB
qJEecnVi0jx/08VxoZpv+e2HA0AcfYC04xeOfgjAe2TT5rEP7mXkS2Xz/XjP7Cvm
qO2jJk0k+g9wDPaHHfRzUXo6P0x5Ztt2PAMGvYMpFI5iQ8l5M4AN2uvlKfAlGZWs
kIY0N/QaONYakX7T6ZPbuR+OFeTr0lB3BZPfx5zRhnSTWIrBHsFJ2Td6bMglSIz8
/SQfLVtDAUF/LirL4An3sEyFAFQvJmeCLWeHCA5eVxHyoyfwaPtm4pRIgar/NS+U
y/0rSSt+e2PHlc34FUf5bOxasgLxdZaWpfhF9Rr+Twg7B2wfaPV0CL5Nu6I5etaZ
+CJcPjI1AVbJx4cl+ez5kKZH8KPfAoICADc0RiYLxM8zFBhf2pFrGa+SxsHwuh9z
fCvoKvCmQ3QW0GditSZWKchfb8VFhSVGTDzZ1lsCdsoYl6ZDsI7ay4chDi1C5Mb5
ybPIPzOGXVp0mDqiley81D535HBQyYWdQyOpikodxgl2Om8n0V4kGE/p9PMgGDF5
sLQKyFJ0NiD7FrscskM9VTFc81J5GXA/DiOSxDo9SzA54kwo3ZN9B7VMSDwvSeb7
vhxvm+M9gBZAYBZ0QsadcFrOsThEyt8O9b4RIXkNLYvtzcRFF1e23X002Dt0FnI7
CEXu0gM0kcMD8FuuN37RZ6HAxT6tYyZn6mJSNPqV/QOV6kqrFuZoggiHVk/DeISf
fuVV0zdKVlDOxFWlCG7cxs+xg7QW0WoK3QUQeK/x74xwqvWpWMOE4BJOWtEcqSfi
xadiFDHdsZi1vlqaheSa/Knt1/RyKxhMnAPI8UaltQa3ZQkfLqs0kQfRsSvTsNFb
J1E5/6qgQWs+000KYqbAPBCNDmrd+EfYjNQ0/ENaGQcrBF5xVFXLb2PJcgPX6yJE
tp6FMRlYUju8Dut/UlS2C6cTZ20i4LBaC9feSaAokBd4j1NTQxWH1APmM6eHhmr+
oMEei4hAoLcaAaCQiY8Ph03hknY1fVqzJ6FbgTXc2ml/JU0k0rp1GcsPFQjZCrS8
a2oBHaavr6wnAoICACaZXH8E66y6frktyTiym9/gT+lW9rPzS+AWJzzZ7z6utI2L
Lg6PpeS4jGCs6FZz+AYzd+vnTQPlmjek6KXd6SfcPtEmO7kexpZJzaKh2NUTy+3U
dFgWK+DgpnlZm7he4sO/jE/jCjweQ1xtNHeh7Rqrrm2l034yYbNLZOZ6e42uHA2j
J/9etUNfMiO6n7wPkSoJIzDx6pIBrVJaL4kAqpAxwCj77vSYmwKml75COITaxf+H
JZCsZJAMhmCASX52zYJoejhOXUxUxzxavDCspWkR0o8y2+3gZfEDE1I0rb1fN6BN
T5IHaMpbGbPIhh3FVIVPhBsa0A3HnhKSWg7w6ShEeIZF8Ogp0OS+xWVdSVScqAJv
8bJAttuT0L3v1wXsgRu814eSaoX97bZq37RTiOFdYNZABISHXHt0+W9B3wm1WQtF
SnQ4wyJzUwQeZXXSF4UkRR+mOZoA0trecUeq+VeQc/Es5Q+sLH2bfKQl0VFGD4MW
W+tGthMArAqLpx1nXgAvS5F8IYryUCNU0rTjxu6b/SA+V5tVM1k4haKsa7axy3pp
WPqI7FCyy9AxohoquKwfHamdUycQJq4A7JccdztOP3nKhO1Pw0RyXVbBDsChl1Qh
8inqZjlyHrdtYRadTq3LEfMCbTmXRVBB+P+4vNCIqvV9VR6V6UpKYaF9yDMj
-----END RSA PRIVATE KEY-----
";
    private static $PUBLIC_KEY  ="-----BEGIN PUBLIC KEY-----
MIIEIjANBgkqhkiG9w0BAQEFAAOCBA8AMIIECgKCBAEAsnqFPJQM0x3RhAbwpKEP
TsfC8mE1gze5iczGbuosVrIlp5q9Ifawp/WHk/ji4q/Uz07Ivkuvm+hbhLB1Jdqq
4stuLmg9cxK9HWKs6nU4QqpceEwE6Imiz38bCPLZU5geYjnBGPBgu7wY9PD/dSwr
xhIB/FuKGtocM/100VfVLYFs7/wAK/WhkGLBPo6YoS3h8+DT9Fqv0jDhcvYYk/IW
LEegO3qCjIl0KCSGSdHvJp5Xrg/BDSpA2oYL+2j9OPPfyfRqUKP4seDPEpFfCnhS
4tx/LfBrJruKf4ztVfcDojjZWztS+Tk7a83/i6USPxbYfqMH0pdKplFVL0x/S2KR
yqU85E4chohtixMtrE9TPqk/xuqb/NFV8bxGPY2YFH4faB9EJrS9QF/vAxRJbR2N
OIbubg3JotkFSOAnAwcg8pjz0PKlP0HeQ2vTtPxzGm2FropVWTPsdm5G1Dlj1nVM
8Ox0CZKZeRsdjsyXLAwMZqKNEUmsneAYDefM9202I+pLnI4eeiAyUEbzkbAxWtQ1
HxjWLaoX7RNMDVdFlmx7OOFME14njfr+Rn+gLs7b3YKSCW5I2hfuJBaL2tXgy+hJ
FjtCO006+Xj54ct+NXJDHSqdCAxhKvIy5UEqOPIfKZeiaEvuYNvDjVL8DTG7lgIe
nwUgvD0+fZESitWlifsuzMEdXMTpek1GJC5UMX+arZX95A+X5KAE3U13zRBYK6Ph
avG0Iqac0yKIILGX+2zI6PN9st533fzSA2I1paS5IyYsMfqdRO6ET0m7W1iTN6S9
ekdFX5cZDIgmfuRBLuUgoSqBygPW/QivryyHrNgO3CPthHg7u5Gdm4gGHOAc3Pwi
a8MlDzxKEU1bw1X4Mk0vzg9S1q+pLToa2BOi2TrSecXMGVCizsxysfggKUhrbmE3
6pMXhgq5QORZBUfzP2MGlYgvh+c1+2p0Rh1QZOY/i+PS5uulP7jWvAz/5osyZLZn
I5eNw9znGdesP9CXeU21V6V/QgRwauywC3WD0Bzo5WsxHQAxkFSt2DY29qsPvgpM
8cRwWWw6+Qudy/znGb7qyF2gO9XjiuUNizf22DPi2T7em7OL30BM8qY8n+sqpXQO
/7WNIDPm+dTd2rEQ7eEjIYKVV2ueEjCZyWdpt82kfftwOqsbWhupFBt8myvtWBub
6xm76muT5t4aXGWmekxpIQNAXIE8Z247DKr5pXYF7ZkGBtDsNRlzLPZSnilKZafi
+x9UW7fkIPqSXMUZK0vbumA5yBNO3rK92G6fiLiZttu65QgcjTXUGHc/fw0iSdaP
frFZXe+TEMzLBbgPXPKI7wHxjrBPyV1Z/XNeGqWuTdF/ZkOkEDZMdfk+j0Iub+xm
fQIDAQAB
-----END PUBLIC KEY-----";
    /**
     * 获取私钥
     * @return bool|resource
     */
    private static function getPrivateKey()
    {
        $privKey = self::$PRIVATE_KEY;
        return openssl_pkey_get_private($privKey);
    }
    /**
     * 获取公钥
     * @return bool|resource
     */
    private static function getPublicKey()
    {

        $publicKey = self::$PUBLIC_KEY;
        return openssl_pkey_get_public($publicKey);
    }
    /**
     * 私钥加密
     * @param string $data 数据
     * @return null|string
     */
    public function privateEncrypt($data = '')
    {
        //  var_dump(self::getPrivateKey());die;
        if (!is_string($data)) {
            return null;
        }
        $encrypted = "";
        openssl_private_encrypt($data,$encrypted,self::getPrivateKey());
        return base64_encode($encrypted);
    }
    
    /**
     * 公钥解密
     */
    public function publicDecrypt($encrypted){
        $decrypted="";
        openssl_public_decrypt(base64_decode($encrypted),$decrypted,self::getPublicKey());
        return $decrypted;
    }
    
    /**
     * 公钥加密
     */
    public function publicEncrypt($data){
        $encrypted="";
        openssl_public_encrypt($data,$encrypted,self::getPublicKey());
        return base64_encode($encrypted);
    }
    
    /**
     * 私钥解密
     */
    public function privateDecrypt($encrypted){
        $decrypted="";
        openssl_private_decrypt(base64_decode($encrypted),$decrypted,self::getPrivateKey());
        return $decrypted;
    }
    
}
?>