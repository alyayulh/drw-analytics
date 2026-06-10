import re
from pathlib import Path
from urllib.parse import urlparse

from locust import HttpUser, task, between


class PengujianSistem(HttpUser):
    wait_time = between(1, 3)

    def on_start(self):
        response = self.client.get("/login")
        csrf_token = self.ambil_csrf_token(response.text)

        self.client.post(
            "/login",
            data={
                "_token": csrf_token,
                "username": "admin",
                "password": "admin123",
            },
            name="Login Admin",
        )

    def ambil_csrf_token(self, html):
        hasil = re.search(r'name="_token"\s+value="([^"]+)"', html)

        if hasil:
            return hasil.group(1)

        hasil = re.search(r'value="([^"]+)"\s+name="_token"', html)

        if hasil:
            return hasil.group(1)

        return ""

    def ambil_action_form_analisis(self, html):
        hasil = re.search(
            r'<form[^>]*id="formAnalisisApi"[^>]*action="([^"]+)"',
            html,
        )

        if not hasil:
            return "/asosiasi/analisis"

        action = hasil.group(1).replace("&amp;", "&")

        if action.startswith("http"):
            parsed = urlparse(action)
            return parsed.path

        return action

    @task(3)
    def dashboard_insight(self):
        self.client.get(
            "/asosiasi/dashboard",
            name="Dashboard Insight",
        )

    @task(2)
    def halaman_analisis_pola(self):
        self.client.get(
            "/asosiasi/analisis",
            name="Analisis Pola",
        )

    @task(1)
    def submit_proses_analisis_dengan_dataset(self):
        response = self.client.get("/asosiasi/analisis")

        csrf_token = self.ambil_csrf_token(response.text)
        action_form = self.ambil_action_form_analisis(response.text)

        lokasi_dataset = Path(__file__).parent / "dataset_testing.xlsx"

        with open(lokasi_dataset, "rb") as file:
            with self.client.post(
                action_form,
                data={
                    "_token": csrf_token,
                    "min_support": "0.01",
                    "min_confidence": "0.4",
                    "min_lift": "1.0",
                },
                files={
                    "file": (
                        "dataset_testing.xlsx",
                        file,
                        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    )
                },
                name="Submit Proses Analisis dengan Dataset",
                catch_response=True,
            ) as response_submit:

                if response_submit.status_code in [200, 302]:
                    response_submit.success()
                else:
                    print("STATUS ERROR:", response_submit.status_code)
                    print(response_submit.text[:500])
                    response_submit.failure(
                        f"Submit dataset gagal. Status: {response_submit.status_code}"
                    )

    @task(2)
    def riwayat_analisis(self):
        self.client.get(
            "/asosiasi/riwayat",
            name="Riwayat Analisis",
        )

    @task(1)
    def detail_riwayat_analisis(self):
        response = self.client.get("/asosiasi/riwayat")

        hasil_link = re.search(
            r'href="([^"]*asosiasi/riwayat/[^"]+)"',
            response.text,
        )

        if hasil_link:
            url_detail = hasil_link.group(1).replace("&amp;", "&")

            if url_detail.startswith("http"):
                parsed = urlparse(url_detail)
                url_detail = parsed.path

            self.client.get(
                url_detail,
                name="Detail Riwayat Analisis",
            )
        else:
            self.client.get(
                "/asosiasi/riwayat",
                name="Detail Riwayat Analisis - Data Tidak Ditemukan",
            )