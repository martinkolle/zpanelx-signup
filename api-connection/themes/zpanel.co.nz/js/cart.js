$(function() {
    $('#client-login').bind('submit',function(event){
        $.post('{{ constant('http://billing.digix1.net/bb-api/rest.php/')',
        $(this).serialize(),
        function(json) {
            if(json.error) {
                alert(json.error.message);
            } else {
                parent.window.location = '{{ constant('http://billing.digix1.net/')';
            }
        }, 'json');
        return false;
    });
});
</script>