sudo myisam_ftdump -c /var/lib/mysql/plantboo_main/taxa_search 3 | sort -rn | head -n 1000 | awk '{print $3}' > /home/caiofior/Scaricati/plantbook.it/shell/words.txt
