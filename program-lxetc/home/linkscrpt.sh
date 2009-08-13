cd ~/.etc/home
for i in *
do
#read  -p "$i: Link? " a
if [ "$i"  != "linkscrpt.sh" ]
then
ln -sf ~/.etc/home/"$i" ~/.$i
fi
done

