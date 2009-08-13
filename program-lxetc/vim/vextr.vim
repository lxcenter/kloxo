function! GdbStep()
	if(!exists("g:gdb_var"))
		call GdbCall("s")
		return
	endif

	let g:gdb_step=1
	call GdbCall("s")
	
	silent! wincmd P
	let mx='\s*\(\S\+\)\s*\(.*\)'
	let lg_var=matchstr(g:gdb_var,mx)
	let var=substitute(lg_var,mx,'\1','')
	let lg_var=substitute(lg_var,mx,'\2','')
	call GdbCall("p",var)

	while (lg_var!="")
		let var=substitute(lg_var,mx,'\1','')
		let lg_var=substitute(lg_var,mx,'\2','')
		call GdbCall("p",var)
	endwhile
	call GdbShowVar()
	unlet g:gdb_step
	if(bufwinnr(".gt_data")==winnr())
		silent! wincmd p
	endif
	cf .gt_line
endfunction


function! GdbDatage(val)
	let cwinn = winnr()

	if(bufwinnr(".gdb") == cwinn)
		wincmd p
		let cwinn = 1
	endif

	if (a:val == 10)
		!cat .gt_ferr
		return
	endif

	let gwinn = bufwinnr(".gt_data")
	if (gwinn == -1)
		if(a:val == 3 || a:val == 1)
			pedit .gt_data
			silent wincmd P
			silent wincmd p
			return
		endif
		if(a:val == 3 || a:val == 2)
			cf .gt_line
			call AsmColor()
		endif
		return
	endif

	let cwinn = winnr()
	if(gwinn == cwinn)
		if( a:val == 3  || a:val == 1)
			silent e! .gt_data
			call GdbDataEnter()
		endif
		if(a:val == 3 || a:val == 2)
			silent 1 wincmd w
			cf .gt_line
			call AsmColor()
			"hi Current ctermfg = Red
			call ColorCurrentBrows()
			silent wincmd P
		endif
	else 
		if( a:val == 3 || a:val == 1)
			silent wincmd P
			silent e! .gt_data
			call GdbDataEnter()
			silent wincmd p
		endif
		if( a:val == 3 || a:val == 2 )
			silent! cf .gt_line
			call AsmColor()
		endif
	endif
endfunction

"if(a:first=="ni" || a:first=="n" || a:first=="s" || a:first=="si" || a:first=="rmt" || a:first=="fs" || a:first=="c" || a:first=="r" || a:first=="u")

"	else
"		silent wincmd p
"		if (exists("g:t_oldwinhc"))
"			let winh = winheight(0) + owinht - 1 - g:t_oldwinhc
"			exe "resize ".  winh
"		else
"			let winh = winheight(0) + owinht - 1
"			exe "resize " . winh
"		endif
"		silent wincmd p
"	endif


