#!/bin/bash
#Filename: query-members.sh
#Description: Read in wp_mepr_members table and compare it to advance_memb.txt file

#mysql -u arnoldarboretumwebsite -pSZipROuoX8Eka3x -D arnoldarboretumwebsite -e "SELECT * FROM wp_mepr_members, wp_users WHERE wp_mepr_members.user_id = wp_users.id AND wp_mepr_members.active_txn_count = 1";
#mysql -u arnoldarboretumwebsite -pSZipROuoX8Eka3x -D arnoldarboretumwebsite -e "SELECT wp_mepr_members.user_id, wp_mepr_members.active_txn_count, wp_users.id, wp_users.user_email FROM wp_mepr_members, wp_users WHERE wp_mepr_members.user_id = wp_users.id AND wp_mepr_members.active_txn_count = 1";


# Get current date
now=$(date +'%x')
echo "CURRENT DATE IS : ${now}"

todate=$(date -d now +%s)
echo "DATE CONVERTED IS : ${todate}"

timestamp=$(date '+%Y-%m-%d %H:%M:%S')
echo "TIMESTAMP : $timestamp"
echo


# Database connection variables
USER='arnoldarboretumwebsite'
PASS='SZipROuoX8Eka3x'
HOST='35.199.33.30'
DATABASE='arnoldarboretumwebsite'
MEPR_MEMBERS_TABLE='wp_mepr_members'
MEPR_TRANSACTIONS_TABLE='wp_mepr_transactions'
POSTS_TABLE='wp_posts'
USERS_TABLE='wp_users'



# -- Functions --
# Create a transaction in the wp_mepr_transactions table
create_mepr_transaction() {
  user_id=$1
  product_id=$2
  start_time=$(date -d "${3}" '+%Y-%m-%d %H:%M:%S')
  end_time=$(date -d "${4}" '+%Y-%m-%d %H:%M:%S')

  # Make sure the transaction number isn't taken
  declare -A transactions
  a=0
  while IFS=$'\t' read trans_num[a++]; do
    :;done < <(mysql -u $USER -p$PASS -D $DATABASE -N \
    -e "SELECT $MEPR_TRANSACTIONS_TABLE.trans_num
    FROM $MEPR_TRANSACTIONS_TABLE")
  ((a--))

  for ((b=0; b<$a; b++))
  do
    transactions[$b]=$(echo ${trans_num[$b]} | cut -d'-' -f 3)
    echo -e "TRANSACTION NUMBERS: ${transactions[$b]}"
  done


  # Generate a new transaction number and check it across that array for duplicates- if duplicate repeat this, else use that transaction number
  repeat=true

  while [ $repeat = true ]; do
    repeat=false
    random_trans_num=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 16 | head -n 1)

    for ((c=0; c<$a; c++))
    do
      if [ "${transactions[$c]}" = "${random_trans_num}" ]; then
        repeat=true
      fi
    done
  done

  new_trans_num="aa-members-$random_trans_num"

  echo -e "NEW TRANSACTION NUMBER ${new_trans_num}"

  # Add this transaction to the table
  mysql -u $USER -p$PASS -D $DATABASE -N -e "INSERT INTO $MEPR_TRANSACTIONS_TABLE
  (\`amount\`,\`tax_shipping\`,\`tax_class\`,\`user_id\`,\`product_id\`,\`coupon_id\`,\`trans_num\`,\`status\`,\`txn_type\`,\`gateway\`,\`subscription_id\`,\`prorated\`,\`created_at\`,\`expires_at\`)
  VALUES (0,1,'standard',$user_id,$product_id,0,\"$new_trans_num\",'complete','payment','manual',0,0,\"${start_time}\",\"${end_time}\")"

  echo
  echo "CREATE A MEPR TRANSACTION IN wp_mepr_transactions TABLE"
  echo -e "USER ID: ${user_id}   PRODUCT ID: ${product_id}   START TIME: ${start_time}   END TIME: ${end_time}"
  echo
}


# Create a user in the wp_users table
create_wp_user() {
  nice_name=$1
  email=$2
  password=$3
  first_name=$4
  last_name=$5

  display_name="${first_name} ${last_name}"

  mysql -u $USER -p$PASS -D $DATABASE -N -e "INSERT INTO $USERS_TABLE
  (\`user_login\`,\`user_pass\`,\`user_nicename\`,\`user_email\`,\`user_registered\`,\`user_status\`,\`display_name\`)
  VALUES (\"$nice_name\",MD5(\"$password\"),\"$nice_name\",\"$email\",\"$timestamp\",0,\"$display_name\")"

  echo
  echo "CREATE A WP USER IN wp_users TABLE"
  echo -e "NICE NAME: ${nice_name}   PASSWORD: ${password}  EMAIL: ${email}   TIME STAMP: ${timestamp}"
  echo
}


# Create a user in the wp_mepr_members table
create_mepr_member() {
  user_id=$1

  mysql -u $USER -p$PASS -D $DATABASE -N -e "INSERT INTO $MEPR_MEMBERS_TABLE
  (\`user_id\`,\`first_txn_id\`,\`latest_txn_id\`,\`txn_count\`,\`expired_txn_count\`,\`active_txn_count\`,\`sub_count\`,\`pending_sub_count\`,\`active_sub_count\`,\`suspended_sub_count\`,\`cancelled_sub_count\`,\`memberships\`,\`last_login_id\`,\`login_count\`,\`total_spent\`,\`created_at\`,\`updated_at\`,\`trial_txn_count\`)
  VALUES (\"$user_id\",0,0,0,0,0,0,0,0,0,0,0,0,0,0,\"$timestamp\",\"$timestamp\",0)"

  echo
  echo -e "ADDED USER $user_id TO MEPR MEMBERS TABLE"
  echo
}
# -- End Functions --



# Query database and store column values in arrays
# This will give us a better product ID
g=0
while IFS=$'\t' read product_name[g] product_id[g++]; do
  :;done < <(mysql -u $USER -p$PASS -D $DATABASE -N \
  -e "SELECT $POSTS_TABLE.post_title, $POSTS_TABLE.ID
  FROM $POSTS_TABLE
  WHERE $POSTS_TABLE.post_type = 'memberpressproduct'")
((g--))


mepr_product_id=${product_id[0]}
#for h in "${product_id[@]}" ; do
#    ((h > mepr_product_id)) && product_id=$h
#done
#echo -e "PRODUCT ID $mepr_product_id"
#echo
for ((h=0; h<$g; h++))
do
  if [ "${product_name[$h]}" = "Members" ]; then
    mepr_product_id=${product_id[$h]}
  fi
done
echo "MEMBERS PRODUCT ID: $mepr_product_id"


## Might need to account for `memberships` field being multiple memberships
#declare -A mepr_membership_id
#declare -A mepr_transaction_num
i=0
while IFS=$'\t' read user_id[i] user_email[i] user_login[i] mepr_user_id[i] mepr_first_txn_id[i] mepr_latest_txn_id[i] mepr_txn_count[i] mepr_active_count[i++]; do #mepr_membership_id[i++]; do  #add to end of SELECT line below, $MEPR_MEMBERS_TABLE.memberships
  :;done < <(mysql -u $USER -p$PASS -D $DATABASE -N \
  -e "SELECT $USERS_TABLE.id, $USERS_TABLE.user_email, $USERS_TABLE.user_login, $MEPR_MEMBERS_TABLE.user_id, $MEPR_MEMBERS_TABLE.first_txn_id, $MEPR_MEMBERS_TABLE.latest_txn_id, $MEPR_MEMBERS_TABLE.txn_count, $MEPR_MEMBERS_TABLE.active_txn_count
  FROM $MEPR_MEMBERS_TABLE, $USERS_TABLE
  WHERE $MEPR_MEMBERS_TABLE.user_id = $USERS_TABLE.id") # AND $TABLE.active_txn_count >= 1;
# Get the list total numbered correctly, might not need this
((i--))


# Iterate over arrays (for testing)
echo "USER ID   USER EMAIL   USER LOGIN   MEPR USER ID   MEPR FIRST TRANSACTION   MEPR LATEST TRANSACTION   MEPR TRANSACTION COUNT   MEPR ACTIVE COUNT   MEPR MEMBERSHIP ID"
for ((j=0; j<$i; j++))
do
  echo -e "${user_id[$j]}\t${user_email[$j]}\t${user_login[$j]}\t${mepr_user_id[$j]}\t${mepr_first_txn_id[$j]}\t${mepr_latest_txn_id[$j]}\t${mepr_txn_count[$j]}\t${mepr_active_count[$j]}\t${mepr_membership_id[$j]}"
done
echo


# Read tsv file and store column values in arrays
declare -A advance_id advance_first_name advance_last_name advance_email advance_category_code advance_category advance_rate_code advance_rate advance_start advance_end advance_exists_in_wp start_timestamp end_timestamp #endConv
k=0
while IFS=$'\n\r\t' read colA colB colC colD colE colF colG colH colI colJ; do
  [ "$colA" == "Entity_id" ] && continue
  advance_id[$k]=${colA}
  advance_first_name[$k]=${colB}
  advance_last_name[$k]=${colC}
  advance_email[$k]=${colD}
  advance_category_code[$k]=${colE}
  advance_category[$k]=${colF}
  advance_rate_code[$k]=${colG}
  advance_rate[$k]=${colH}
  advance_start[$k]=${colI}
  advance_end[$k]=${colJ}
  advance_exists_in_wp[$k]=false
  #endConv[$k]=$(date -d "$colG -7 days" +'%s')
  start_timestamp[$k]=$(date -d "$colI" '+%Y-%m-%d %H:%M:%S')
  end_timestamp[$k]=$(date -d "$colJ" '+%Y-%m-%d %H:%M:%S')
  kind_end[$k]=$(date -d "$colJ" '+%B %e %Y')
  ((k = k+1))
done < advance_memb.txt


# Iterate over arrays (for testing)
echo -e 'ID\t\tFIRSTNAME\tLASTNAME\tEMAIL\tMemberCatCode\tMemberCategory\tMemberRateCode\tMemberRate\tSTART\tEND\tSTARTTIMESTAMP\tENDTIMESTAMP\tEXISTS IN WP'
for ((l=0; l<$k; l++))
do
  echo -e "${advance_id[$l]}\t${advance_first_name[$l]}\t${advance_last_name[$l]}\t${advance_email[$l]}\t${advance_category_code[$l]}\t${advance_category[$l]}\t${advance_rate_code[$l]}\t${advance_rate[$l]}\t${advance_start[$l]}\t${advance_end[$l]}\t${start_timestamp[$l]}\t${end_timestamp[$l]}\t${advance_exists_in_wp[$l]}" #${endConv[$l]}
done
echo


# Compare the date and decide if that email needs to be found and the active_txn_count column set to 0
for ((j=0; j<$i; j++))
do
  for ((l=0; l<$k; l++))
  do

    # If there is an email attached to the advance record
    if [ "${advance_email[$l]}" != "N/A" ]; then

      # If the user exists in both Advance printout and Wordpress database
      if [ "${advance_email[$l]}" = "${user_email[$j]}" ]; then
        echo "FOUND EMAIL ${advance_email[$l]}, their wordpress account exists"
        advance_exists_in_wp[$l]=true

        # If the user has no active transactions, create it
        if [ "${mepr_active_count[$j]}" = 0 ]; then
          echo "THEY HAVE NO ACTIVE ACCOUNTS"
          ((mepr_txn_count[$j]++))
          echo -e "TRANSACTION COUNT ${mepr_txn_count[$j]}"

          # Create a transaction
          create_mepr_transaction ${user_id[$j]} ${mepr_product_id} ${advance_start[$l]} ${advance_end[$l]}
##          create_mepr_transaction ${user_id[$j]} ${mepr_product_id} ${advance_start[$l]} ${advance_end[$l]}
  #        mysql -u $USER -p$PASS -D $DATABASE -N -e "INSERT INTO $MEPR_TRANSACTIONS_TABLE
  #        (\`amount\`,\`tax_shipping\`,\`tax_class\`,\`user_id\`,\`product_id\`,\`coupon_id\`,\`status\`,\`txn_type\`,\`gateway\`,\`subscription_id\`,\`prorated\`,\`created_at\`,\`expires_at\`)
  #        VALUES (0,1,'standard',${user_id[$j]},${mepr_product_id},0,'complete','payment','manual',0,0,\"${start_timestamp[$l]}\",\"${end_timestamp[$l]}\")"


          # Get that latest transactions ID
          echo "GET LATEST TRANSACTION ID"
          m=0
          while IFS=$'\t' read transaction_id[m++]; do
            :;done < <(mysql -u $USER -p$PASS -D $DATABASE -N \
            -e "SELECT $MEPR_TRANSACTIONS_TABLE.id
            FROM $MEPR_TRANSACTIONS_TABLE
            WHERE user_id = \"${user_id[$j]}\" AND created_at = \"${start_timestamp[$l]}\"")
          ((m--))

          for ((n=0; n<$m; n++))
          do
            echo -e "TRANSACTION ID ${transaction_id[$n]}"
            echo
          done


          # Update the members table to have the active values
          # If the first subscription
          if [ "${mepr_first_txn_id[$j]}" = "0" ]; then
            echo "UPDATE MEMBERS TABLE FOR FIRST TRANSACTION"
            mysql -u $USER -p$PASS -D $DATABASE -N -e "UPDATE $MEPR_MEMBERS_TABLE
            SET first_txn_id = '${transaction_id[0]}', latest_txn_id = '${transaction_id[0]}', txn_count = '${mepr_txn_count[$j]}', active_txn_count = '1', memberships = '$mepr_product_id'
            WHERE user_id = ${user_id[$j]}"
            echo

            # Send an email about the updated account
            echo -e "Hello ${advance_first_name[$l]},\n\nYour Arnold Arboretum members account has been renewed.\n\nYour account is now set to expire on ${kind_end[$l]}.\n\nPlease login to at https://arboretum.harvard.edu/login.\n\nThank you,\nArnold Arboretum" | mail -s "Updated your Arnold Arboretum account ${user_login[$j]}" ${user_email[$j]}
            echo "SENT AN ACTIVATE EMAIL TO ${user_email[$j]}"
            echo


          # If already had an active subscription
          else
            echo "UPDATE MEMBERS TABLE FOR THIS TRANSACTION"
            mysql -u $USER -p$PASS -D $DATABASE -N -e "UPDATE $MEPR_MEMBERS_TABLE
            SET latest_txn_id = '${transaction_id[0]}', txn_count = '${mepr_txn_count[$j]}', active_txn_count = '1', memberships = '$mepr_product_id'
            WHERE user_id = ${user_id[$j]}"
            echo

            # Send an email about the updated account
            echo -e "Hello ${advance_first_name[$l]},\n\nYour Arnold Arboretum members account has been renewed.\n\nYour account is now set to expire on ${kind_end[$l]}.\n\nPlease login to at https://arboretum.harvard.edu/login.\n\nThank you,\nArnold Arboretum" | mail -s "Updated your Arnold Arboretum account ${user_login[$j]}" ${user_email[$j]}
            echo "SENT AN UPDATE EMAIL TO ${user_email[$j]} FOR LATEST TRANSACTION"
            echo
          fi


        # If the user has active transactions, update it's expiration date
         ## THIS WILL BE WHERE TO EXPAND WITH MULTIPLE MEMBERSHIP TYPES
        else
          echo "THEY HAVE AN ACTIVE ACCOUNT, UPDATE END TIMESTAMP"
          echo -e "TRANSACTION COUNT ${mepr_txn_count[$j]}"

          mysql -u $USER -p$PASS -D $DATABASE -N -e "UPDATE $MEPR_TRANSACTIONS_TABLE
          SET expires_at = \"${end_timestamp[$l]}\"
          WHERE user_id = ${user_id[$j]}"

          # Send an email about the updated account
          #echo -e "Updated your Arnold Arboretum account ${user_login[$j]}" | mail -s "Arnold Arboretum members account updated.\n\nPlease login at https://arboretum.harvard.edu/login.\n\nThank you,\nArnold Arboretum" ${user_email[$j]}
          #echo "SENT AN UPDATE EMAIL TO ${user_email[$j]}"
          #echo
        fi

      fi

    fi
  done
done
echo


# Iterate over arrays (for testing)
echo -e 'ID\t\tFIRSTNAME\tLASTNAME\tEMAIL\tMemberCatCode\tMemberCategory\tMemberRateCode\tMemberRate\tSTART\tEND\tSTARTTIMESTAMP\tENDTIMESTAMP\tEXISTS IN WP'
for ((l=0; l<$k; l++))
do
  echo -e "${advance_id[$l]}\t${advance_first_name[$l]}\t${advance_last_name[$l]}\t${advance_email[$l]}\t${advance_category_code[$l]}\t${advance_category[$l]}\t${advance_rate_code[$l]}\t${advance_rate[$l]}\t${advance_start[$l]}\t${advance_end[$l]}\t${start_timestamp[$l]}\t${end_timestamp[$l]}\t${advance_exists_in_wp[$l]}" #${endConv[$l]}
done
echo


# If no user exists in the Wordpress, create a new user in the Wordpress database and give them a transaction
for ((l=0; l<$k; l++))
do
  if [ "${advance_exists_in_wp[$l]}" = true ]; then
    echo -e "Found user ${advance_id[$l]} ${advance_first_name[$l]} ${advance_last_name[$l]} ${advance_email[$l]}"

  else
    echo -e "Couldn't find user ${advance_id[$l]} ${advance_first_name[$l]} ${advance_last_name[$l]} ${advance_email[$l]}"

    if [[ "${advance_email[$l]}" == *"@"* ]]; then
      nice_name=$(echo ${advance_email[$l]} | cut -d'@' -f 1)
      echo
      echo -e "Nice name: ${nice_name}"

      # Generate a random password
      password=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 16 | head -n 1)
      echo -e "CREATE NEW USER FOR THIS EMAIL ${advance_email[$l]}"

      create_wp_user $nice_name ${advance_email[$l]} $password ${advance_first_name[$l]} ${advance_last_name[$l]}

      # Get that latest user ID
      echo -e "GET NEW USER ID FOR $nice_name"

      o=0
      while IFS=$'\t' read newest_user_id[o++]; do
        :;done < <(mysql -u $USER -p$PASS -D $DATABASE -N \
        -e "SELECT $USERS_TABLE.ID
        FROM $USERS_TABLE
        WHERE user_email = \"${advance_email[$l]}\" AND user_registered = \"$timestamp\"")
      ((o--))

      for ((p=0; p<$o; p++))
      do
        echo -e "USER ID ${newest_user_id[$p]}"
      done

      # Create the mepr_members table user entry
      create_mepr_member ${newest_user_id[0]}

      create_mepr_transaction ${newest_user_id[0]} ${mepr_product_id} ${advance_start[$l]} ${advance_end[$l]}
#        mysql -u $USER -p$PASS -D $DATABASE -N -e "INSERT INTO $MEPR_TRANSACTIONS_TABLE
#        (\`amount\`,\`tax_shipping\`,\`tax_class\`,\`user_id\`,\`product_id\`,\`coupon_id\`,\`status\`,\`txn_type\`,\`gateway\`,\`subscription_id\`,\`prorated\`,\`created_at\`,\`expires_at\`)
#        VALUES (0,1,'standard',${user_id[$j]},${mepr_product_id},0,'complete','payment','manual',0,0,\"${start_timestamp[$l]}\",\"${end_timestamp[$l]}\")"


      # Get that latest transactions ID
      q=0
      while IFS=$'\t' read transaction_id[q++]; do
        :;done < <(mysql -u $USER -p$PASS -D $DATABASE -N \
        -e "SELECT $MEPR_TRANSACTIONS_TABLE.id
        FROM $MEPR_TRANSACTIONS_TABLE
        WHERE user_id = \"${newest_user_id[0]}\" AND created_at = \"${start_timestamp[$l]}\"")
      ((q--))

      for ((r=0; r<$q; r++))
      do
        echo -e "TRANSACTION ID ${transaction_id[$r]}"
      done

      mysql -u $USER -p$PASS -D $DATABASE -N -e "UPDATE $MEPR_MEMBERS_TABLE
      SET first_txn_id = '${transaction_id[0]}', latest_txn_id = '${transaction_id[0]}', txn_count = '1', active_txn_count = '1', memberships = '${mepr_product_id}'
      WHERE user_id = \"${newest_user_id[0]}\""

      # Send the confirmation email
      echo -e "Hello, ${advance_first_name[$l]},\n\nYour membership account for the Arnold Arboretum has been created and is set to expire on ${kind_end[$l]}.\n\nLog in at https://arboretum.harvard.edu/login.\n\nYour username is ${nice_name} and your password is ${password}.\nPlease update your password when you login the first time.\n\nThank you,\nArnold Arboretum" | mail -s "Welcome to your Arnold Arboretum membership" ${advance_email[$l]}
      echo "SENT AN UPDATE EMAIL TO ${advance_email[$l]}"
      echo
    fi

  fi
done
