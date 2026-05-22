namespace Agora.DAL.Query
{
    class NodeQueries
    {
        public const string GetNodeRoot = @"
            SELECT node.*, template.name AS NameTemplate
            FROM node
            INNER JOIN template on template.IdTemplate = node.IdTemplate
            WHERE Not Exists(Select IdNode from node_relation WHERE node_relation.IdNode = node.IdNode)
            ORDER BY ModifyDate DESC";
        public const string GetNode = "SELECT * FROM node WHERE IdNode =  @IdNode";
        public const string GetNodeByIdNodeRoot = "SELECT * FROM node WHERE IdNodeRoot =  @IdNodeRoot";
        public const string InsertNode = @"
        INSERT INTO node (IdNode, Name, JsonValue, IdTemplate, ModifyDate)
        VALUES (@IdNode, @Name, @JsonValue, @IdTemplate, NOW());";
        //"SELECT IdNode FROM node ORDER BY IdNode DESC LIMIT 1;"

        public const string UpdateNode = @"
        UPDATE node 
            SET Name = @Name, JsonValue = @JsonValue, IdTemplate = @IdTemplate, `ModifyDate` = NOW()
        WHERE IdNode = @IdNode;";
        public const string DeleteNode = @"DELETE FROM node  WHERE IdNode = @IdNode;";
        public const string DeleteNodes = @"DELETE FROM node  WHERE IdNode in @IdNodes;";
        public const string GetNodesListByIdNodeRoot = @"
        SELECT node.*, template.name AS NameTemplate, node_relation.IdNodeParent, IFNULL(node_relation.Place,0) AS `Order`
        FROM node
        INNER JOIN template on template.IdTemplate = node.IdTemplate
        LEFT join node_relation  on node_relation.IdNode = node.IdNode
        WHERE @IdNode IN (node.IdNode,node_relation.IdNodeRoot)
        ORDER BY node.IdNode";
    }
}