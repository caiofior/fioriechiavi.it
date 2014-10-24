#!/bin/bash
mysqldump -u root --no-data flora | sed 's/ AUTO_INCREMENT=[0-9]*//g' > 1.sql 
