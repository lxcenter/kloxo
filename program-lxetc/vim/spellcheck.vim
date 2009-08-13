""""""""""""""""""""""""""""""""""""""""""""""""""
" Syntax Checker
" 
" Version: $Revision: 1.1.1.1 $
" Id     : $Id: spellcheck.vim,v 1.1.1.1 2003/03/02 12:11:58 ligesh Exp $
" Date   : September 2001
"
" Author : Matthias Veit <matthias_veit@yahoo.de>
"""""""""""""""""""""""""""""""""""""""""""""""""""



function SpellCheck()
ruby << RUBYBLOCK
	#setup syntax highligting
	VIM::command("syn match BADWORD \"BADWORD\"")
	VIM::command("syn clear BADWORD")
	VIM::command("highlight BADWORD term=bold ctermfg=Red guibg=Orange guifg=Black")
	buffer = VIM::Buffer.current
	aspell = IO.popen("aspell -t -l", "w+") #--language-tag=de_DE 
	1.upto(buffer.count) { |linenr| 
	  aspell.puts(buffer[linenr])
	}
	aspell.close_write
	aspell.each { |badword|
	  badword.chomp!
	  VIM::command("syn match BADWORD \"\\<#{badword}\\>\"")
	}
RUBYBLOCK
endfunction 

function Propose()
ruby << RUBYBLOCK
	cword = VIM::evaluate("expand(\"<cword>\")")
	aspell = IO.popen("aspell -a","w+")
	aspell.puts(cword)
	aspell.close_write
	aspell.readlines.each{ |line|
	  if (line=~/^#/)
		print("Sorry, no proposals for #{cword}\n")
	  elsif (line=~/^\*/)
		print("#{cword} is correct!\n")
	  elsif (line=~/^&.*:\s*(.+)/)
		print("Proposal(s) for #{cword}\n")
		print("-------------------------------\n")
		$1.split(/,\s*/).each { |word|
		  print(" #{word}\n")
		}
	  end
	}
RUBYBLOCK
endfunction 
"Matthias
function AddToDictionary()
ruby << RUBYBLOCK
	#change this to fit your own private dictionary
	privatedic = ENV["HOME"]+"/.aspell.german.pws"
	cword = VIM::evaluate("expand(\"<cword>\")")
	content = File.readlines(privatedic)
	header = content[0]
	header =~ /(\S+\s\S+\s)(\d+)/
	content[0]="#{$1}#{$2.to_i+1}\n"
	content.push(cword+"\n")
	dictionary = File.open(privatedic,"w+")
	dictionary.write(content)
	dictionary.close
RUBYBLOCK
endfunction 

function! SpchkNxt()
	let badword   = synIDtrans(hlID("BADWORD"))
	let lastline= line("$")
	let curcol  = 0
	norm w
	while synIDtrans(synID(line("."),col("."),1)) != badword
	  norm w
	  if line(".") == lastline
		let prvcol=curcol
		let curcol=col(".")
		if curcol == prvcol
		  break
		endif
	  endif
	endwhile
	unlet curcol
	unlet badword
	unlet lastline
	if exists("prvcol")
	  unlet prvcol
	endif
endfunction

function! SpchkPrv()
    let badword = synIDtrans(hlID("BADWORD"))
    let curcol= 0
    norm b
    while synIDtrans(synID(line("."),col("."),1)) != badword
      norm b
      if line(".") == 1
        let prvcol=curcol
        let curcol=col(".")
        if curcol == prvcol
          break
        endif
      endif
    endwhile
    unlet curcol
    unlet badword
    if exists("prvcol")
      unlet prvcol
    endif
endfunction

nmap \p :call Propose()<cr>
nmap \c :call SpellCheck()<cr>
nmap \a :call AddToDictionary()<cr>


amenu 190.80.10 E&xtended.Spell.&check :call SpellCheck()<cr>
amenu 190.80.20 E&xtended.Spell.&clear :syn clear BADWORD<cr>
amenu 190.80.30 E&xtended.Spell.&proposal :call Propose()<cr>
amenu 190.80.40 E&xtended.Spell.&add\ word :call AddToDictionary()<cr>

