
"set scrolloff=10

"map <space> <C-f>

"call CompileMap(6)
"map	<M-c>	<M-a>
"imap	<M-c>	<M-a>
"au FileChangeShell  *.out e!
"au BufEnter rc.local :cf rclocal.tag

"set tags+=/usr/src/linux/tags

function! GdbModule()
	let a = system("cd ~/.etc/gdb ; ls ")
	while a != ''
		let val = substitute(a, '\(\w\)\s*\(.*\)', '\1', '')
		let a = substitute(a, '\(\w\)\s*\(.*\)', '\2', '')
	endwhile
endfunction

function! LoadResModuletmp()
	!bsdloadmodule.sh ~/wh/inc/code/bsd/pci/a.o
endfunction

function! LoadModuletmp()
	!loadmodule.sh ~/wh/pub/src/linux/comedi/comedi/comedi.o
	!loadmodule.sh ~/wh/pub/src/linux/comedi/comedi/drivers/mite.o
	!loadmodule.sh ~/wh/pub/src/linux/comedi/comedi/drivers/8255.o
	!loadmodule.sh ~/wh/pub/src/linux/comedi/comedi/drivers/ni_pcimio.o
	"!ssh test.lxlabs.com comedi_config /dev/comedi0 ni_pcimio
	"Ide vintr
	sleep 2
	call GdbModulesource()
endfunction

function! GdbResModulesource()
	let tmpvar = g:gdb_sync
	let g:gdb_sync = 1
	Ide source ~/wh/inc/code/bsd/pci/.scrgdb/a
	let g:gdb_sync = tmpvar
endfunction

function! GdbModulesource()
	let tmpvar = g:gdb_sync
	let g:gdb_sync = 1
	Ide source ~/.etc/.tmp/comedi
	Ide source ~/.etc/.tmp/mite
	Ide source ~/.etc/.tmp/8255
	Ide source ~/.etc/.tmp/ni_pcimio
	let g:gdb_sync = tmpvar
endfunction

"map <F50>cm :call LoadModuletmp()<CR>
"map <F50>cs :call GdbModulesource()<CR>
map <F50>cm :call LoadResModuletmp()<CR>
"map <F50>cs :call GdbResModulesource()<CR>

au BufRead */bayonne*/* set tags=tags,./tags,~/wh/pub/work/ccscript-2.2.1/tags,~/wh/pub/work/commoncpp2-1.0.0/tags








" **************************** vtmp ***********************

"* ~/.etc/vim/vcpp  *
"* ~/.etc/vim/vfunc *
"* ~/.etc/vim/vimrc *
"* ~/.etc/vim/vmaps *
"* ~/.etc/vim/vmeta *
"* ~/.etc/vim/vwin  *
"* ~/.etc/vim/vtmp  *
"* ~/.etc/vim/vcols *
