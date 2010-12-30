

Important Rules:

 Form Variables should never have the form "__path"; $gbl->__path* is reserverd for system paths and thus the http variables wont be searched for them.


 q = quota Variable
 Q = Variables that are collected and summed from the children and stored in parent; These variables have no limit. Sort of Dead Resource collection.
 L = List Quota variables. Variables that are actually multiple in number, and they are enforced in such a way that the client never gets anything that is not in the parent.
 s = list (select)
 A = associative Array. The key of array will be the value of option, and the value of array is the display of option
 e = enum (select)
 f = flag variable
 m = modifiable
 M = Non Modifiable
 n = needed
 t = textarea, editable, and submitable
 T = textarea, noneditable and nonsub
 E = Existing or New
 U = muliselect
 p = percentage
 b = psuedo variable that is used as a button
 F = file... Used for upload

 Flags IN children (_l and _o stuff)

 R = check for resource
 L = Loginnable Children. (Used for Tickets)
 q = check for quota
 d = delete
 t = toggle.
 b = backup
 B = backup And Displayable in the main menu
 r = readonly. They are not checked when the entire object heirarcy is written to the db/system.

 Flags in Class Descriptors

 S = Show Class (Default Action)
 U = Update Class
 P = Parent CLass (ffile). It means a lot. The p means that it is a virtual list. That is it is sort of an object, yet it is treated as a list. Check the getFromVirtualList function
 N = It means that nname should be used in determining the unique name and image name etc. Again used for ffile, which should show different descriptions depending on the nname, rather than just class alone.

 Reserverd endings.
 _l = child list
 _o = child object
 _f = fake variable
 _m = mulitple updates
 _qlist = Quota List



 IN vlist (Add update forms)
  __v_button = button name
  __v_message(post-pre) = messages


  If you set a variable to 'null', 'isset' will return false. You have to set it to blank string ("");
