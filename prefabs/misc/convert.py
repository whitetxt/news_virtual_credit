contents = open("flags.txt").readlines()

out = open("flags.php", "w")
out.write("<?php\n")
out.write("$regions = array(\n")

for stuff in contents:
	parts = stuff.split("\t")
	if len(parts) < 3:
		continue
	flag = parts[0].strip()
	name = parts[1].strip()
	out.write(f'\t"{name}" => "{flag}",\n')

out.write(");\n")
out.write("?>")