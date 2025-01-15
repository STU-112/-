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
        .header {
            display: flex;
            background-color: rgb(220, 236, 245);
        }
        .header nav {
            text-align: right;
            width: 100%;
            font-size: 100%;
            text-indent: 10px;
        }
        .header nav a {
            font-size: 30px;
            color: rgb(39, 160, 130);
            text-decoration: none;
            display: inline-block;
            line-height: 52px;
        }
        .header nav a:hover {
             background-color: #ffaa00;
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        tr.second-row {
            background-color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        caption {
            font-size: 1.5em;
            margin: 10px;
            font-weight: bold;
        }
        .banner {
            width: 100%;
            background: linear-gradient(to bottom, #e8dff2, #f5e8fc);
            color: #333;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 10px 20px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
        }
        .banner a {
            color: #5a3d2b;
            text-decoration: none;
            font-weight: bold;
			text-align:left;
            font-size: 1.2em;
        }
		
        .banner a:hover {
            color: #007bff;
        }
    </style>