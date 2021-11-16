#!/bin/sh
newclient () {
        dir="/var/www/html/"
        cp /etc/openvpn/client-template.txt $dir/$1.ovpn
        echo "<ca>" >> $dir/$1.ovpn
        cat /etc/openvpn/easy-rsa/pki/ca.crt >> $dir/$1.ovpn
        echo "</ca>" >> $dir/$1.ovpn
        echo "<cert>" >> $dir/$1.ovpn
        cat /etc/openvpn/easy-rsa/pki/issued/$1.crt >> $dir/$1.ovpn
        echo "</cert>" >> $homeDir/$1.ovpn
        echo "<key>" >> $dir/$1.ovpn
        cat /etc/openvpn/easy-rsa/pki/private/$1.key >> $dir/$1.ovpn
        echo "</key>" >> $dir/$1.ovpn
        echo "<tls-crypt>" >> $dir/$1.ovpn
        cat /etc/openvpn/tls-crypt.key >> $dir/$1.ovpn
        echo "</tls-crypt>" >> $dir/$1.ovpn
}

                        cd /etc/openvpn/easy-rsa/
                        ./easyrsa build-client-full $1 nopass
                        newclient $1
exit
;;
