<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            height: 100%;
            width: 100%;
            font-family: 'Noto Sans TC', Arial, sans-serif;
            background: linear-gradient(to bottom, #e8dff2, #f5e8fc);
            color: #333;
        }
        /* 表格樣式 */
        table {
            width: 50%;
            margin: 20px auto;
            border-collapse: collapse;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        table, th, td {
            border: 2px solid #e0e0e0;
        }
        th {
            background-color: #DEFFAC;
            color: black;
            font-weight: bold;
            text-align: center;
            padding: 12px;
        }
        td {
            text-align: left;
            padding: 12px;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:nth-child(odd) {
            background-color: #ffffff;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        caption {
            font-size: 1.6em;
            font-weight: bold;
            margin: 15px;
            color: #333;
        }
        textarea {
            width: 95%;
            height: 80px;
            margin: 15px auto;
            resize: none;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px;
            font-size: 1em;
        }
        .button-container {
            text-align: center;
            margin-top: 15px;
        }
        button {
            padding: 10px 15px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            color: white;
            background-color: #4CAF50;
        }
        button[type='button'] {
            background-color: #999;
        }
        button:hover {
            background-color: #45a049;
        }
        button[type='button']:hover {
            background-color: #666;
        }
		.banner {
            width: 100%;
            background: linear-gradient(to bottom, #e8dff2, #f5e8fc); /* 淡紫色漸層 */
            color: #333;
            display: flex;
           justify-content: flex-start;
            align-items: center;
            padding: 10px 20px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2); /* 陰影效果 */
        }
        .banner a {
            color: #5a3d2b;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2em;
        }
        .banner a:hover {
            color: #007bff; /* 當滑鼠懸停時變換顏色 */
        }
    </style>