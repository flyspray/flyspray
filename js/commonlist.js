TableControl.create("listTable", {
        controlBox: "controlBox",
        tree: false
})

new Draggable("controlBox",{
        handle: "grip"
})

document.getElementById('controlBoxUp').addEventListener('click', function(event) {
        TableControl.up('listTable');
        event.preventDefault();
}, false);

document.getElementById('controlBoxDown').addEventListener('click', function(event) {
        TableControl.down('listTable');
        event.preventDefault();
}, false);
