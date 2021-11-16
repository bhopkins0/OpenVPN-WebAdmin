#!/bin/sh
revokeClient() {
        CLIENT=$1
        cd /etc/openvpn/easy-rsa/ || return
        ./easyrsa --batch revoke "$CLIENT"
        EASYRSA_CRL_DAYS=3650 ./easyrsa gen-crl
        rm -f /etc/openvpn/crl.pem
        cp /etc/openvpn/easy-rsa/pki/crl.pem /etc/openvpn/crl.pem
        chmod 644 /etc/openvpn/crl.pem
        sed -i "/^$CLIENT,.*/d" /etc/openvpn/ipp.txt
}

revokeClient $1

exit
;;
