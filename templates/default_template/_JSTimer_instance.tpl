<script>
CreateChronoApplet('{Type}', '{Ref}', {InsertTime}{ReverseChrono});
var ChronoInterval{Type}{Ref} = setInterval("CreateChronoApplet('{Type}', '{Ref}', {InsertTime}{ReverseChrono}{InsertCallback})", 500);
</script>