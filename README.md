#### Working with social networks:

##### The application includes:

 Functionality for working with the command line **http://social.icontext.ru/cmd/commands**
 Facebook.

 1. Authorization and authentication;
- It is necessary to enter the password of the FB user with whom the application will work;
- Assignment of access rights, in accordance with the account and assigned privileges to work with the application;
 
2. Getting a bundle: PAGE -> FORM -> LEADS from FB:
- Getting all allowed application pages;
- Selection of all allowed forms, with leads linked to them;
- Writing leads to the lead form file and to the temporary table tmp_fb_leads
- Signing the lead page to receive data from FB, in case they are changed or added;

3. Subscribing to the **leadgen** event from FB 
- Clicking on an unlabeled line (white) initiates a subscription;
- Clicking on the marked line (green) deletes the subscription;

4. The "Next" button allows you to go to the hierarchy department and add mail for the lead;
 - Open: FORM -> LEAD -> mailbox input field;
 - Adding a mailbox /mailboxes to which fresh leads will be sent;
 
  
Getting Leads:

- from the project folder, using the console command:

  **bin/console facebook:get-leads**

- kamanda initiates uploading leads to the database, as well as to .csv files

Affected tables and files:

**tmp_fb_leads, fb_leads, /tmp/***

Sending Leads:

- from the project folder, using the console command:

  **bin/console facebook:send-leads**

Requested data:

fb_form_mails (to those mailboxes where the lead was not received), in conjunction with fb_leads

   - Errors, in case of missing data: 
      
      **HTTP/1.1 204 HTTP NO CONTENT**
    
   ```json 
        {
             error: "No events where detected"
        }    
   ```
