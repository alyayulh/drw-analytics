document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formAnalisis");
    const fileInput = document.getElementById("file_excel");
    const fileName = document.getElementById("fileName");
    const btnProses = document.getElementById("btnProses");
    const btnReset = document.getElementById("btnReset");
    const loadingCard = document.getElementById("loadingAnalisis");
    const steps = document.querySelectorAll(".process-step");

    if (!form) return;

    fileInput.addEventListener("change", function () {
        if (fileInput.files.length > 0 && fileName) {
            fileName.textContent = fileInput.files[0].name;
        }
    });

    if (btnReset) {
        btnReset.addEventListener("click", function () {
            fileInput.value = "";

            if (fileName) {
                fileName.textContent = "Product Sales Details - DRW SKIN CARE BEAUTY - All Outlets - 01 Jan 2026 - 31 Mar 2026.xls";
            }

            steps.forEach(function (step) {
                step.classList.remove("active", "done");
            });

            if (loadingCard) {
                loadingCard.classList.add("hidden");
            }

            btnProses.disabled = false;
            btnProses.innerHTML = "▷ Proses Analisis";
        });
    }

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        if (!fileInput.files.length) {
            alert("Silakan pilih file Excel terlebih dahulu.");
            return;
        }

        btnProses.disabled = true;
        btnProses.innerHTML = "Memproses...";

        if (loadingCard) {
            loadingCard.classList.remove("hidden");
            loadingCard.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        }

        steps.forEach(function (step) {
            step.classList.remove("active", "done");
        });

        let currentStep = 0;

        function updateStep() {
            steps.forEach(function (step, index) {
                step.classList.remove("active", "done");

                if (index < currentStep) {
                    step.classList.add("done");
                } else if (index === currentStep) {
                    step.classList.add("active");
                }
            });

            currentStep++;

            if (currentStep <= steps.length) {
                setTimeout(updateStep, 700);
            } else {
                steps.forEach(function (step) {
                    step.classList.remove("active");
                    step.classList.add("done");
                });

                setTimeout(function () {
                    form.submit();
                }, 700);
            }
        }

        updateStep();
    });
});