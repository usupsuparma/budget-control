# 📘 PIP New API Documentation

Base URL:

```
http://localhost/pip_new/api
```

---

## 🔐 Authentication

Beberapa endpoint membutuhkan Bearer Token:

```
Authorization: Bearer <token>
```

---

## 📌 Standard Response Format

```json
{
  "success": true,
  "message": "string",
  "total": 0,
  "data": []
}
```

---

# 📊 Endpoints

---

## 1. Get PPH

**GET** `/get_pph.php`

### Response

```json
{
  "success": true,
  "message": "Data PPH berhasil diambil",
  "total": 9,
  "data": [
    {
      "id": 0,
      "nama": "0%",
      "pph": 0,
      "label": "0%"
    }
  ]
}
```

### Fields

| Field | Type    | Description   |
| ----- | ------- | ------------- |
| id    | integer | ID PPH        |
| nama  | string  | Nama PPH      |
| pph   | number  | Nilai PPH     |
| label | string  | Label display |

---

## 2. Get Tax

**GET** `/get_tax.php?keyword=pph`

### Query Params

| Param   | Type   | Description      |
| ------- | ------ | ---------------- |
| keyword | string | Filter pencarian |

### Response

```json
{
  "success": true,
  "message": "Data tax berhasil diambil",
  "total": 4,
  "data": [
    {
      "id": 6,
      "code": "pph21",
      "tax": "PPH 21",
      "label": "PPH 21"
    }
  ]
}
```

### Fields

| Field | Type    |
| ----- | ------- |
| id    | integer |
| code  | string  |
| tax   | string  |
| label | string  |

---

## 3. Get PPN

**GET** `/get_ppn.php?keyword=11`

### Query Params

| Param   | Type   |
| ------- | ------ |
| keyword | string |

### Response

```json
{
  "success": true,
  "message": "Data PPN berhasil diambil",
  "total": 3,
  "data": [
    {
      "id": 3,
      "nama": "11 %",
      "ppn": 0.11,
      "label": "11 %"
    }
  ]
}
```

### Fields

| Field | Type    |
| ----- | ------- |
| id    | integer |
| nama  | string  |
| ppn   | number  |
| label | string  |

---

## 4. Get Vendor

**GET** `/get_vendor.php?keyword=mandiri`

### Query Params

| Param   | Type   |
| ------- | ------ |
| keyword | string |

### Response

```json
{
  "success": true,
  "message": "Data vendor berhasil diambil",
  "total": 17,
  "data": [
    {
      "id": 31,
      "no": "31110.A0031",
      "name": "ASE MANDIRI",
      "label": "31110.A0031 - ASE MANDIRI"
    }
  ]
}
```

### Fields

| Field | Type    |
| ----- | ------- |
| id    | integer |
| no    | string  |
| name  | string  |
| label | string  |

---

## 5. Get Jenis Transaksi

**GET** `/get_jenis_transaksi.php?keyword=biaya`

### Query Params

| Param   | Type   |
| ------- | ------ |
| keyword | string |

### Response

```json
{
  "success": true,
  "message": "Data jenis transaksi berhasil diambil",
  "total": 424,
  "data": [
    {
      "id": 300,
      "kode": "6000.1.1.01.01",
      "jenis_transaksi": "Pengeluaran biaya Hydrogen Gas (H2 Gas)",
      "label": "Pengeluaran biaya Hydrogen Gas (H2 Gas)"
    }
  ]
}
```

### Fields

| Field           | Type    |
| --------------- | ------- |
| id              | integer |
| kode            | string  |
| jenis_transaksi | string  |
| label           | string  |

---

## 6. Get Jenis Kas

**GET** `/get_jenis_kas.php?keyword=kas`

### Query Params

| Param   | Type   |
| ------- | ------ |
| keyword | string |

### Response

```json
{
  "success": true,
  "message": "Data Jenis Kas berhasil diambil",
  "total": 22,
  "data": [
    {
      "id": 2,
      "kode": "1100.0.0.00.00",
      "nama": "Cash On Hand And At Bank",
      "label": "1100.0.0.00.00 - Cash On Hand And At Bank"
    }
  ]
}
```

### Fields

| Field | Type    |
| ----- | ------- |
| id    | integer |
| kode  | string  |
| nama  | string  |
| label | string  |

---

## 7. Submit Pengeluaran Reguler

**POST** `/pengeluaran_reguler_submit.php`

🔐 Requires Bearer Token

### Headers

```http
Authorization: Bearer <token>
Content-Type: application/json
```

---

### Request Body

```json
{
  "tgl": "2026-04-15",
  "jenis_kas": "1100.1.1.00.00",
  "currency": "IDR",
  "rate": 16500,
  "keterangan": "Pembayaran operasional kantor",
  "items": [
    {
      "jenis_transaksi": "1810.3.2.01.04",
      "cost_center_code": "CC001",
      "vendor_id": "12",
      "reff": "INV-001",
      "value": 1000000,
      "ppn": 0.11,
      "ppnval": 110000,
      "tot": "PPh23",
      "pph": 0.02,
      "pphval": 20000,
      "tax_trx_id": "TAX-001"
    },
    {
      "jenis_transaksi": "1810.3.2.01.04",
      "cost_center_code": "CC002",
      "vendor_id": "15",
      "reff": "INV-002",
      "value": 2000000,
      "ppn": 0.11,
      "ppnval": 220000,
      "tot": "PPh23",
      "pph": 0.02,
      "pphval": 40000,
      "tax_trx_id": "TAX-002"
    }
  ]
}
```

---

### Request Fields

#### Root Object

| Field      | Type          | Required | Description                  |
| ---------- | ------------- | -------- | ---------------------------- |
| tgl        | string (date) | ✅        | Tanggal transaksi            |
| jenis_kas  | string        | ✅        | Kode jenis kas               |
| currency   | string        | ✅        | Mata uang (contoh: IDR, USD) |
| rate       | number        | ✅        | Kurs                         |
| keterangan | string        | ❌        | Keterangan transaksi         |
| items      | array         | ✅        | Detail item transaksi        |

---

#### Items Object

| Field            | Type   | Required | Description                 |
| ---------------- | ------ | -------- | --------------------------- |
| jenis_transaksi  | string | ✅        | Kode jenis transaksi        |
| cost_center_code | string | ✅        | Kode cost center            |
| vendor_id        | string | ✅        | ID vendor                   |
| reff             | string | ❌        | Referensi (invoice/dokumen) |
| value            | number | ✅        | Nilai transaksi             |
| ppn              | number | ❌        | Persentase PPN              |
| ppnval           | number | ❌        | Nilai PPN                   |
| tot              | string | ❌        | Jenis pajak (misal: PPh23)  |
| pph              | number | ❌        | Persentase PPh              |
| pphval           | number | ❌        | Nilai PPh                   |
| tax_trx_id       | string | ❌        | ID transaksi pajak          |

---

### Response

```json
{
  "success": true,
  "message": "Pengeluaran reguler berhasil disimpan",
  "data": {
    "id_transaksi": "BP001009",
    "doc_type": "BP - Bank Payment",
    "doc_no": "BP0.005280",
    "total": 3270000,
    "total_usd": 198.1818181818182,
    "items_count": 2
  }
}
```

---

### Response Fields

| Field        | Type    | Description                |
| ------------ | ------- | -------------------------- |
| id_transaksi | string  | ID transaksi               |
| doc_type     | string  | Jenis dokumen              |
| doc_no       | string  | Nomor dokumen              |
| total        | number  | Total dalam currency utama |
| total_usd    | number  | Total dalam USD            |
| items_count  | integer | Jumlah item                |

---

