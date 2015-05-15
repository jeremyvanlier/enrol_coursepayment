// prevent instant delete
Y.all('.delete').on('click' , function(e){
    var status = confirm('Are you sure?');
    if(status){
        return;
    }

    e.preventDefault();
});