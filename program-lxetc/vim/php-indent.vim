" Vim indent file
" Language:	PHP
" Author:	John wellesz <John.wellesz (AT) teaser (DOT) fr> inspired from the original version
"  by Miles Lott <milos (AT) groupwhere (DOT) org>
" URL:		http://www.2072productions.com/vim/indent/php.vim
" Last Change:	2004 November 5th
" Version: 1.02
"
"
" Changes: 1.02:	The bug I was talking about in version 1.01 (right below) has
"					been corrected :)
"					Also corrected another bug that could occur in
"					some special cases.
"					I removed the debug mode left in 1.01 that could
"					cause some Vim messages at loading if other script were
"					bugged.
"
" Changes: 1.01:	Some little bug corrections reguarding automatic optimized
"					mode that missed some tests and could break the indenting.
"
"					There is also a problem with complex non bracked structures, when several
"					else are following each other, the algorithm do not indent the way it
"					should.
"					That will be corrected in the next version.
" 
" Changes: improvments with regard to the original version (0.5) by Miles Lott:
"			   - Commented part of code or non PHP code no longer break the
"				 indent algorithm, the content of those parts are indented
"				 separatly
"			   - corrected a strange thing (a part of code was duplicated) ->
"			   the last version couldn't work.
"		       - Folds can be used without problem
"		       - multilevel non bracked structures are indented (like
"		       in C)
"		         Exemple:
"					if (!isset($History_lst_sel)) 
"						if (!isset($blabla)) 
"							if (!isset($bloum)) {
"								$History_lst_sel=0;
"							} else
"							    $foo="truc";
"						else $bla= "foo";
"					$command_hist = TRUE;
"
"			   - "array( 'blabla'," lines no longer break the indent of the
"			     following lines
"			   - the options php_noindent_switch and php_indent_shortopentags have been removed
"			     (couldn't find any reason why one would want to use them)
"			   - PHP open and close tags are always set to col 1 as for the
"			   immediate following php code
"			   
" Changes: (previous versions by Miles Lott)
"
"		   0.5 - fix duplicate indent on open tag, and empty bracketed
"          statements.
"          0.4 - Fixes for closing php tag, switch statement closure, and php_indent_shortopentags
"          option from Steffen Bruentjen <vim (AT) kontraphon (DOT) de>
"
"
"
"  If you find a bug, please e-mail me at John.wellesz (AT) teaser (DOT) fr
"  with an example of code that break the algorithm.
"
"
"	Thanks a lot for using this script.
"


" Only load this indent file when no other was loaded. But reset those state
" variables if needed

let s:lastindented = 0
let s:indentbeforelast = 0
let s:indentinghuge = 0
let s:CurrentIndentLevel = 0
let s:LastIndentedWasComment = 0

if exists("b:did_indent")
	finish
endif
let b:did_indent = 1
setlocal nosmartindent

setlocal nolisp
setlocal indentexpr=GetPhpIndent()
"setlocal indentkeys+=0=,0),=EO
setlocal indentkeys=0{,0},0),:,!^F,o,O,e

" Only define the function once.
if exists("*GetPhpIndent")
	finish
endif

"TODO:	FIX THE PROBLEM THAT WILL OCCUR IF A BLOCK STARTER IS DEFINED OVER
"		SEVERAL LINES (maybe include && || as starters too...)
"update: partially fixed

"TODO:	Detect /**/ comment type and format them properly ie: don't loose time
"		on them


let s:endline= '\s*\(//.*\|#.*\|/\*.*\*/\s*\)\=$'
"setlocal  debug=msg


function! GetLastRealCodeLNum(startline) " {{{
	"Inspired from the function SkipJavaBlanksAndComments by Toby Allsopp for indent/java.vim 
	let lnum = a:startline
	while lnum > 1
		let lnum = prevnonblank(lnum)
		" Non PHP code is considered as comment
		if getline(lnum) =~ '\(\*/\|\(//\s*\)\@<!<?\(php\)\=\)\s*$'   " if the end of a comment we need to find the beginning
			while getline(lnum) !~ '/\*\|?>' && lnum > 1 || getline(lnum) =~ '\(?>.*\)\@<!<?' && lnum > 1 " while still inside comment
				let lnum = lnum - 1
			endwhile
			if getline(lnum) =~ '^\s*\(/\*\|?>\)' " if line contains nothing but comment
				let lnum = lnum - 1
			else
				break
			endif
		elseif getline(lnum) =~ '^\s*\(//\|#\)' " if line is under comment
			let lnum = lnum - 1
		else
			break
		endif
	endwhile
	return lnum
endfunction
" }}}

function! Skippmatch()  " {{{
   	" the slowest instruction of this script, remove it and the script is 3
	" times faster but you may have troubles with '{' inside comments or strings
	" that will break the indent algorithm...
	let synname = synIDattr(synID(line("."), col("."), 0), "name")
	if synname =~? "string" || synname =~? "phpComment"
		return 1
	else
		return 0
	endif
endfun
" }}}

function! FindOpenBracket(lnum) " {{{
	call cursor(a:lnum, 1) " set the cursor to the start of the lnum line
	return searchpair('{', '', '}', 'bW', 'Skippmatch()')
endfun
" }}}

function! FindTheIfOfAnElse (lnum, StopAfterFirstPrevElse) " {{{
" A very clever recoursive function created by me (John Wellesz) that find the "if" corresponding to an
" "else". This function can easily be adapted for other languages :)
	
	if getline(a:lnum) =~# '^\s*}\s*else\(if\)\=\>'
		let beforeelse = a:lnum " we do this so we can find the opened bracket to speed up the process
	else
		let beforeelse = GetLastRealCodeLNum(a:lnum - 1)
	endif

	if !s:level
		let s:iftoskip = 0
	endif

	" If we found another "else" then it means we need to skip the next "if"
	" we'll found. (since version 1.02)
	if getline(beforeelse) =~# '^\s*\(}\s*\)\=else\(\s*if\)\@!\>'
		let s:iftoskip = s:iftoskip + 1
	endif
	
	" A closing bracket? let skip the whole block to save some recursive calls
	if getline(beforeelse) =~ '^\s*}' " .s:endline
		let beforeelse = FindOpenBracket(beforeelse)

		" Put us on the block starter
		if getline(beforeelse) =~ '^\s*{'
			let beforeelse = GetLastRealCodeLNum(beforeelse - 1)
		endif
	endif


	if !s:iftoskip && a:StopAfterFirstPrevElse && getline(beforeelse) =~# '^\s*\([}]\s*\)\=else\(if\)\=\>'
		return beforeelse
	endif
	" if there was an else, then there is a if...
	if getline(beforeelse) !~# '^\s*if\>' && beforeelse>1 || s:iftoskip && beforeelse>1
		
		if  s:iftoskip && getline(beforeelse) =~# '^\s*if\>'
			let s:iftoskip = s:iftoskip - 1
		endif

		let s:level =  s:level + 1
		let beforeelse = FindTheIfOfAnElse(beforeelse, a:StopAfterFirstPrevElse)
	endif

	return beforeelse

endfunction
" }}}


let s:blockstart = '\(\(\(}\s*\)\=else\)\=if\>\|while\>\|for\(each\)\=\>\|declare\|||\|&&\>\)'

function! GetPhpIndent()
	"##############################################
	"########### MAIN INDENT FUNCTION #############
	"##############################################

	" Let's detect if we are indenting just one line or more than 3 lines
	" in the last case we can slightly optimize our algorithm
	if !s:indentinghuge && s:lastindented > s:indentbeforelast 
		if s:indentbeforelast
			let s:indentinghuge = 1
			echom 'Large indenting detected, speed optimizations engaged'
		endif
		let s:indentbeforelast = s:lastindented
	endif

	" If the line we are indenting isn't directly under the previous non-blank
	" line of the file then deactivate the optimization procedure
	if s:indentinghuge && prevnonblank(v:lnum - 1) != s:lastindented
		echom 'Large indenting deactivated'
		let s:indentinghuge = 0
		let s:indentbeforelast = 0
		let s:lastindented = 0
		let s:CurrentIndentLevel = 0
		let s:LastIndentedWasComment=0
	elseif v:lnum > s:lastindented
		let s:lastindented = v:lnum
	endif

	let s:level = 0

	let cline = getline(v:lnum) " current line

	if s:indentinghuge && cline =~'^\s*//'
		if s:LastIndentedWasComment==1
			return indent(s:lastindented) "  line replaced in 1.02
			" return s:CurrentIndentLevel
		endif
		let s:LastIndentedWasComment=1
	else
		let s:LastIndentedWasComment=0
	endif

	" Find an executable php code line above the current line.
	let lnum = GetLastRealCodeLNum(v:lnum - 1)

	" Hit the start of the file, use zero indent.
	if lnum == 0
		return 0
	endif
	let cline = getline(v:lnum) " current line
	let last_line = getline(lnum)    " last line
	let ind = indent(lnum) " by default
	let endline= s:endline

	" PHP start tags are always at col 1, useless to indent
	if cline =~# '^<?\(php\)\=' && cline !~ '^?>'
		return 0
	endif

	" PHP end tags are always at col 1, useless to indent
	if  cline =~ '^?>' && cline !~# '^<?\(php\)\='
		return 0
	endif

	" if the last line is a stated line and it's not indented then why should
	" we indent this one??
	" if optimized mode active and nor current or previous line are an 'else'
	" or the end of an bracketless thing then indent the same as the previous
	" line
	if last_line =~ '[;}]'.endline
		if s:indentinghuge && ind==s:CurrentIndentLevel && cline !~# '^\s*\(else\|[})];\=\)' && last_line !~# '^\s*\(\(}\s*\)\=else\)'
			return s:CurrentIndentLevel
		elseif ind==0
			return 0
		endif
	endif

	" Search the matching open bracket (with searchpair()) and set the indent of cline
	" to the indent of the matching line.
	if cline =~ '^\s*}\(}}\)\@!'
		let ind = indent(FindOpenBracket(v:lnum))
		" let s:CurrentIndentLevel = ind " removed in 1.02
		let s:CurrentIndentLevel = 0 " added in 1.02, optimized mode could do bad things in some cases
		return ind
	endif

	let LastLineClosed = 0
	"let unstated='[^;{}]'.endline
	let unstated='\(;'.endline.'\)\@<!\(^\s*'.s:blockstart.'.*)\|e'.'lse\>\)'.endline " LOL it appears that VIM script syntax script have some bugs...

	" if the current line is a 'else' starting line
	" (to match an 'else' preceded by a '}' is irrelevant and futile)
	if ind && cline =~# '^\s*else\(if\)\=\>'
		let s:CurrentIndentLevel = 0 " line added in version 1.02 to prevent optimized mode from acting in some special cases
		return indent(FindTheIfOfAnElse(v:lnum, 1))
	elseif last_line =~# unstated && cline !~ '^\s*{\|^\s*);\='.endline
		let ind = ind + &sw
		return ind

		" If the last line is terminated by ';' or if it's a closing '}'
		" We need to check if this isn't the end of a multilevel non '{}'
		" structure such as:
		" Exemple: 
		"			if ($truc)
		"				echo 'truc';
		"
		"	OR
		"
		"			if ($truc)
		"				while ($truc) {
		"					echo 'infinite loop\n'
		"				}
	elseif ind && last_line =~ '\(^\s*)\)\@<!;'.endline.'\|^\s*}\(.*{'. endline.'\)\@!'
		let previous_line = last_line
		let last_line_num = lnum
		let LastLineClosed = 1

		while 1
			" if it's an end of block
			if previous_line =~ '^\s*}'
				" find the openning '{'
				let last_line_num = FindOpenBracket(last_line_num)

				" repeat the procedure till we get to the first '{'
				if getline(last_line_num) =~ '^\s*}'
					continue

					" We've found the first, but are we in a multilevel
					" non bracketted structure ?
					" lets check the line before the last (the one with the '}') to see if it's a
					" 'if ($foo)' style line
				elseif  getline(GetLastRealCodeLNum(last_line_num - 1)) =~ '{'.endline
					" So, since we are still inside the block
					" set a default indent that will match the last line
					" one
					let last_match = last_line_num
					break
				endif

				" We are in an multilevel non '{}' structure
				" find the line before the last '{' so the 'else' below can
				" countinue the job
				let last_match = last_line_num
				let last_line_num = GetLastRealCodeLNum(last_line_num - 1)
				let previous_line = getline(last_line_num)
			else
				" We are here to find the first 'if ($foo)' style block of
				" the structure
				"
				" as long as we are on a 'else(if) xxxxxx' line, jump to its 'if'
				if getline(last_line_num) =~# '^\s*else\(if\)\=\>'
					let last_line_num = FindTheIfOfAnElse(last_line_num, 0)
					continue " re-run the loop (we could find a '}' again)
				endif
				" record the if ($foo) style line
				let last_match = last_line_num
				" Check the line right before to see if we have finihed
				let last_line_num = GetLastRealCodeLNum(last_line_num - 1)
				let previous_line = getline(last_line_num)
				" not a if ($foo) style line then we have finished :D
				if previous_line !~# unstated
					break
				endif
			endif
		endwhile

		if indent(last_match) != ind " if nothing was done lets the old script continue
			let ind = indent(last_match) " return to previous level
			let s:CurrentIndentLevel = 0 " line added in version 1.02 to prevent optimized mode
										 " from acting in some special cases
			"let s:CurrentIndentLevel = ind " line added in 1.01 and removed in 1.02 - wrong solution
			return ind
		endif
	endif

	let plinnum = GetLastRealCodeLNum(lnum - 1)
	let pline = getline(plinnum) " previous to last line

	" Indent blocks enclosed by {} or () or case statements (default
	" indenting)
	if LastLineClosed==0 && last_line =~# '\(//.*\)\@<!\([{(]\|case.*:\)'.endline || LastLineClosed==0 && pline !~ '[,(]'.endline && last_line =~? '[a-z_]\w*\s*(.*,$'
		let ind = ind + &sw
		" return if the current line is not another case statement of the previous line is a bracket open
		if  LastLineClosed==0 && last_line =~ '[{(]'.endline || cline !~# '.*case.*:\|default:'
			let s:CurrentIndentLevel = ind
			return ind
		endif
	endif
	if cline =~# '^\s*);\=\|^\s*case.*:\|^\s*default:'
		let ind = ind - &sw
		" if the last line is a break or return, or the current line is a close bracket,
		" or if the previous line is a default statement, subtract another
		if pline =~ '^\s*default:' && last_line =~# '^\s*break;\|^\s*return' && cline =~ '^\s*);\='
			let ind = ind - &sw
		endif
	endif

	if last_line =~# '^\s*default:'
		let ind = ind + &sw
	endif

	let s:CurrentIndentLevel = ind
	return ind
endfunction

" vim: set ts=4 sw=4:
