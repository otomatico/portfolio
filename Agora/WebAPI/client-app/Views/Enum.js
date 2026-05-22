export const status = {
	None: 0,
	Inserted: 1,
	Deleted: 2,
	Updated: 3
}

export const typeField =
[
	{
		"idType": 0,
		"field": "None"
	},
	{
		"idType": 1,
		"field": "Boleano"
	},
	{
		"idType": 2,
		"field": "Numero"
	},
	{
		"idType": 3,
		"field": "Texto"
	},
	{
		"idType": 4,
		"field": "Seleccion"
	},
	{
		"idType": 5,
		"field": "Seleccion Multiple"
	},
	{
		"idType": 6,
		"field": "Recurso"
	}
];

let typeEnum ={}
typeField.forEach(m=>typeEnum[m.field.trim()]= m.idType)
export const typeFieldEnum = typeEnum
