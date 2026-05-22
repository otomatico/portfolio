namespace Agora.DAL.Query
{
    class NodeRelationQueries
    {
        public const string GetNodeRelation = @"
            SELECT * FROM node_relation WHERE node_relation.IdNode = @IdNode";
        public const string GetNodeRelationByIdNodeRoot = @"
            SELECT * FROM node_relation WHERE node_relation.IdNodeRoot = @IdNode
            ORDER BY IdRelation DESC";
        public const string GetNodeRelationByIdNodeParent = @"
        with recursive nodeRelation AS(
            SELECT * FROM node_relation
            WHERE node_relation.IdNodeParent = @IdNode
            UNION
            SELECT node_relation.*
            FROM node_relation
            INNER JOIN  nodeRelation ON node_relation.IdNodeParent = nodeRelation.IdNode
        )
        SELECT * FROM nodeRelation;";
        public const string InsertNodeRelation = @"
        INSERT INTO node_relation (IdNode, IdNodeParent,  IdNodeRoot, Place)
        Select @IdNode, @IdNodeParent, @IdNodeRoot, (COALESCE(MAX(Place), 0)+1)
        FROM node_relation WHERE IdNodeParent = @IdNodeParent;";
        public const string UpdateNodeRelation = @"
        UPDATE node_relation 
            SET IdNode = @IdNode,
            IdNodeParent = @IdNodeParent,
            IdNodeRoot = @IdNodeRoot,
            Place = @Place
        WHERE IdRelation = @IdRelation;";
        public const string DeleteNodeRelationByIdNodeRoot = @"DELETE FROM node_relation  WHERE IdNodeRoot = @IdNode;";
        public const string DeleteNodeRelationByIdNodeParent = @"DELETE FROM node_relation  WHERE IdNodeParent = @IdNode OR IdNode = @IdNode;";
    }
}