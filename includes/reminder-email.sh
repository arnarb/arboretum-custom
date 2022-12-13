#!/bin/bash
#Filename: reminder-email.sh
#Description: Send reminder emails for events

#mysql -u arnoldarboretumwebsite -pSZipROuoX8Eka3x -D arnoldarboretumwebsite -e "SELECT * FROM wp_mepr_members, wp_users WHERE wp_mepr_members.user_id = wp_users.id AND wp_mepr_members.active_txn_count = 1";
#mysql -u arnoldarboretumwebsite -pSZipROuoX8Eka3x -D arnoldarboretumwebsite -e "SELECT wp_mepr_members.user_id, wp_mepr_members.active_txn_count, wp_users.id, wp_users.user_email FROM wp_mepr_members, wp_users WHERE wp_mepr_members.user_id = wp_users.id AND wp_mepr_members.active_txn_count = 1";


# Get current date