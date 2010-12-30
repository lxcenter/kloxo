
 A parked domain is a domain that shares the document root the main domain that it points to. It is not a redirection, but rather sharing of the document roots at the core level itself. All the scripts will automatically work, but with the new domain name. A [b] 'source.com/directory' [/b]  internally goes to [b] 'destination.com/directory' [/b] , though in the address bar of the browser, the url visible would be [b] source.com/directory. [/b] 

 A redirected domain on the other hand consists of a full redirection of [b] source.com[/b]  to  [b] 'destination.com/directory [/b]. The url in the address bar will also change to  [b] 'destination.com/directory' [/b] .


The mails sent to parked/redirected domain are transparently forwarded to the same accounts in the main domain. For instance, [b] user@parked.com [/b] is transparently sent to [b] user@domain.com [/b]. So if you want to add a mailaccount to a parked/redirected domain, all you need to do is create that user in the main domain, and you will start receiving the mails from the same user parked/redirected domains.

 If you want to edit the DNS of the parked, just modify the dns of the main domain, and they are transparently copied to the parked domain too. So if you want to add a subdomain to the parked domain, just add the same subdomain to the main domain DNS.

