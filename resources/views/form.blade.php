<form action="{{ route('module.generate') }}" method="POST">
    @csrf
    <div id="fields">
        <div class="field">
            <input type="text" name="fields[0][name]" placeholder="Name" required>
            <input type="text" name="fields[0][name_field]" placeholder="Name Field" required>
            <select name="fields[0][type]" required>
                <option value="string">String</option>
                <option value="integer">Integer</option>
            </select>
        </div>
    </div>
    <button type="button" id="add-field">+</button>

    <div>
        <input type="text" name="singular_name" placeholder="Singular Module Name" required>
        <input type="text" name="plural_name" placeholder="Plural Module Name" required>
        <input type="text" name="display_name" placeholder="Display Module Name" required>
    </div>

    <button type="submit">Generate Module</button>
</form>
<style>
    form {
        max-width: 600px;
        margin: 50px auto;
        padding: 20px;
        background-color: #f7f7f7;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    form div {
        margin-bottom: 20px;
    }

    input[type="text"], select {
        width: calc(100% - 20px);
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-bottom: 10px;
        background-color: #fff;
    }

    input[type="text"]:focus, select:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }

    button[type="submit"], #add-field {
        display: inline-block;
        padding: 10px 15px;
        font-size: 16px;
        color: #fff;
        background-color: #28a745;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button[type="submit"]:hover, #add-field:hover {
        background-color: #218838;
    }

    #add-field {
        background-color: #007bff;
    }

    #add-field:hover {
        background-color: #0056b3;
    }

    .field {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .field input[type="text"], .field select {
        flex: 1;
        margin-right: 10px;
    }

    .field:last-child input[type="text"], .field:last-child select {
        margin-right: 0;
    }

    @media (max-width: 768px) {
        .field {
            flex-direction: column;
        }

        .field input[type="text"], .field select {
            width: 100%;
            margin-right: 0;
            margin-bottom: 10px;
        }

        .field:last-child input[type="text"], .field:last-child select {
            margin-bottom: 0;
        }
    }

</style>
<script>
    let fieldIndex = 1;
    document.getElementById('add-field').addEventListener('click', function () {
        let fieldsContainer = document.getElementById('fields');
        let newField = document.createElement('div');
        newField.classList.add('field');
        newField.innerHTML = `
            <input type="text" name="fields[${fieldIndex}][name]" placeholder="Name" required>
            <input type="text" name="fields[${fieldIndex}][name_field]" placeholder="Name Field" required>
            <select name="fields[${fieldIndex}][type]" required>
                <option value="string">String</option>
                <option value="integer">Integer</option>
            </select>
        `;
        fieldsContainer.appendChild(newField);
        fieldIndex++;
    });
</script>
