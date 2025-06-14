<div x-data="{ ping: -1 }" x-init="setInterval(() => {
    const start = Date.now();
    
    @this.call('getPing').then(() => {
        ping = Date.now() - start;
    });
}, 3000)">
    <span x-text="ping"></span> ms
</div>
