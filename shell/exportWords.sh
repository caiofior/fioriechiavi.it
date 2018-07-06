sudo myisam_ftdump -c /var/lib/mysql/plantboo_main/taxa_search 3 | sort -rn | head -n 5000 | awk '{print $3}' > /home/caiofior/Scaricati/florae.it/shell/words.txt
