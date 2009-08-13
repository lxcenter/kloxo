 
 An addon domain is a domain that shares the document root the main domain that it points to. It is not a redirection, but rather sharing of the document roots at the core level itself. So all the scripts will automatically work, but with the new domain name. The mails sent to addon domain are transparently forwarded to the same accounts in the main domain. For instance, [b] user@addon.com [/b] is transparently sent to [b] user@domain.com [/b].

 If you want to add a forwarded domain, you have to create a normal domain in kloxo and then use go to [b] manage site' [/b] -> [b] Redirects [/b], and redirect the '/' to the other domain. We will be adding a proper 'redirected' domain feature shortly.
