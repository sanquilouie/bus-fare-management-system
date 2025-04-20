<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Thermal Receipt</title>
  <style>
    @media print {
      @page {
        size: 58mm auto;
        margin: 0;
      }

      body {
        margin: 0;
        padding: 0;
        font-family: monospace;
        font-size: 12px;
        width: 58mm;
      }

      .receipt {
        //padding: 5px;
        width: 100%;
      }

      .receipt h2,
      .receipt p {
        margin: 0;
        padding: 0;
        text-align: center;
      }

      .items {
        text-align: left;
        margin-top: 10px;
        margin-bottom: 10px;
      }

      .item-line {
        display: flex;
        justify-content: space-between;
      }

      hr {
        border: none;
        border-top: 1px dashed #000;
        //margin: 10px 0;
      }

      .no-print {
        display: none;
      }
    }

    /* Screen preview style */
    body {
      font-family: monospace;
      background: #f5f5f5;
      //padding: 20px;
    }

    .no-print {
      margin-top: 20px;
    }
  </style>
</head>
<body>

  <div class="receipt">
    <h2>RamStar</h2>
    <p>Zaragosa</p>
    <p>Date: 2025-04-20</p>
    <hr>
    <div class="items">
      <div class="item-line"><span>Item 1</span><span>₱100.00</span></div>
      <div class="item-line"><span>Item 2</span><span>₱50.00</span></div>
      <div class="item-line"><span>Item 3</span><span>₱25.00</span></div>
    </div>
    <hr>
    <div class="item-line"><strong>Total</strong><strong>₱175.00</strong></div>
    <hr>
    <p>Thank you for shopping!</p>
    <p>Visit again soon</p>
  </div>

  <button onclick="window.print()" class="no-print">🖨️ Print Receipt</button>

</body>
</html>
