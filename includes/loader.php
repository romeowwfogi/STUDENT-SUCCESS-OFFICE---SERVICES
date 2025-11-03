<style>
    #page-loader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgb(0 0 0 / 80%);
        backdrop-filter: blur(3px);
        z-index: 9999;
        /* display: flex; */
        align-items: center;
        justify-content: center;
    }

    .spinner-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    #page-loader .spinner {
        width: 48px;
        height: 48px;
        border: 4px solid #ccc;
        border-top: 4px solid #fff;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    #loader-text {
        color: white;
        font-size: 16px;
        font-weight: 500;
        text-align: center;
    }

    #loader-text .dots::after {
        content: '...';
        display: inline-block;
        width: 1em;
        text-align: left;
        animation: dots 1.5s steps(4, end) infinite;
    }

    @keyframes dots {

        0%,
        20% {
            content: '';
        }

        40% {
            content: '.';
        }

        60% {
            content: '..';
        }

        80%,
        100% {
            content: '...';
        }
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<div id="page-loader">
    <div class="spinner-container">
        <div class="spinner"></div>
        <p id="loader-text">Please Wait <span class="dots"></span></p>
    </div>
</div>

<script>
    const resendTextElement = document.querySelector('.resend-text');
    let countdownElement = document.getElementById('countdown');
    const pageLoader = document.getElementById('page-loader');

    function showLoader() {
        pageLoader.style.display = 'flex';
    }

    function hideLoader() {
        pageLoader.style.display = 'none';
    }
</script>