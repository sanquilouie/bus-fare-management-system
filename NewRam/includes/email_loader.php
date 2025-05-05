<style>
#loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255,255,255,0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    z-index: 9999;
    font-family: sans-serif;
}

.spinner {
    border: 6px solid #f3f3f3;
    border-top: 6px solid #3498db;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin-bottom: 15px;
}
</style>
<div id="loader" style="display: none;">
    <div class="spinner"></div>
    <p>Sending confirmation email, please wait...</p>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector("form");
        form.addEventListener("submit", function () {
            document.getElementById("loader").style.display = "flex";
        });
    });
</script>