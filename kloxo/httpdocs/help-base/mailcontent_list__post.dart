
  Spam training works by feeding the spam filter a typical spam or ham (non-spam) message. This is primarily used to correct misclassification. So if the spam filter has classified a spam message as non spam, then feed the message back to the filter as 'spam'. The internal database of the filter will be updated, and the next time, it will correctly identify this message as spam. The more messages you feed the filter, the more accurate will be its judgement.


<%ifblock:isadmin%>
 There are two word databases for spam training. The system and the user specific. The admin will be able to train both the databases. The user can only train his own specific database. To train the user specific wordlist, use the [b] train as spam/ham [/b]. To train the system wide wordlist use [b] Train as system spam/ham [/b] 
 </%ifblock%>
